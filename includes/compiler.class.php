<?php
// Copyright 2011 JMB Software, Inc.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//    http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

define('S_PHP', '<?PHP');
define('E_PHP', '?>');
define('NEWLINE', "\n");


if( !class_exists('selectbuilder') )
{
    require_once('common.php');
}

class Compiler
{
    var $current_line = 1;
    var $current_file = null;
    var $left_delimiter = '{';
    var $right_delimiter = '}';
    var $tag_stack = array();
    var $capture_stack = array();
    var $syntax_ok = TRUE;
    var $errors = array();
    var $from_count = 0;
    var $nocache_buffer = '';
    var $nocache_token = '';
    var $template_dir = '';
    var $compile_dir = '';

    function Compiler()
    {
        $this->template_dir = realpath(dirname(__FILE__) . '/../templates');
        $this->compile_dir = $this->template_dir . '/compiled';
    }

    function compile(&$source, &$compiled)
    {
        $this->current_line = 1;
        $this->errors = array();
        $this->from_count = 0;
        $this->nocache_buffer = '';
        $this->nocache_token = '';
        $this->tag_stack = array();
        $this->capture_stack = array();

        $ldq = preg_quote($this->left_delimiter, '~');
        $rdq = preg_quote($this->right_delimiter, '~');

        $source = str_replace(array("\r\n", "\r"), "\n", $source);

        // Process each line of the file
        foreach( explode("\n", $source) as $line )
        {
            $line = "$line\n";

            // Extract and parse all template tags
            $generated_code = preg_replace_callback("~{$ldq}\s*(.*?)\s*{$rdq}~s", array(&$this, 'compile_tag'), $line);

            $compiled .= $generated_code;
            $this->nocache_buffer .= $generated_code;
            $this->current_line++;
        }

        // Process nocache placeholders
        if( preg_match_all("~{$ldq}nocache ([a-z0-9]+){$rdq}(.*?){$ldq}/nocache ([a-z0-9]+){$rdq}~msi", $compiled, $matches, PREG_SET_ORDER) )
        {
            foreach( $matches as $match )
            {
                $cache_id = $match[1];
                $code = $match[2];
                $compiled = str_replace(array("{codecache $cache_id}", "{nocache $cache_id}", "{/nocache $cache_id}"), array(base64_encode($code), '', ''), $compiled);
            }
        }

        // Check for unclosed tag(s)
        if( count($this->tag_stack) > 0 )
        {
            $last_tag = end($this->tag_stack);
            $this->syntax_error("unclosed tag \{{$last_tag[0]}} (opened on line {$last_tag[1]}).");
            return;
        }

        $this->code_cleanup($compiled);

        return $this->syntax_ok;
    }

    function code_cleanup(&$compiled)
    {
        // remove unnecessary close/open tags
        $compiled = preg_replace('~\?> +<\?php~i', ' echo " "; ', $compiled);
        $compiled = preg_replace('~\?><\?php~i', ' ', $compiled);

        // Add extra newline for php closing tags that are at the end of the line
        if( preg_match('~^email~', $this->current_file) )
        {
            $compiled = preg_replace('~\?>$~im', "?>\n", $compiled);
        }
    }

    function compile_file($filename, &$compiled)
    {
        $this->current_file = basename($filename);
        $source = file_get_contents($filename);
        return $this->compile($source, $compiled);
    }

    function compile_tag($matches)
    {
        // Comment
        if( substr($matches[1], 0, 1) == '*' && substr($matches[1], -1) == '*' )
            return '';

        // Parse tag into command, modifiers, and arguments;
        $tag = $this->parse_tag($matches[1]);

        // Don't monkey with stuff when we're inside a {literal} or {php} tag
        list($open_tag) = end($this->tag_stack);
        if( $open_tag == 'literal' && $tag['tag'] != '/literal' )
            return $matches[0];
        if( $open_tag == 'php' && $tag['tag'] != '/php' )
            return $matches[0];


        // Tag name is a variable
        if( $tag['tag'][0] == '$' )
        {
            $_return = $this->parse_vars($tag['tag'] . '|' . $tag['modifiers']);
            return S_PHP . " echo $_return; " . E_PHP;
        }


        // Determine what to do with this tag
        switch($tag['tag'])
        {
            case 'if':
            {
                $this->push_tag('if');
                return $this->compile_if_tag($tag['attributes']);
            }

            case 'else':
            {
                list($open_tag) = end($this->tag_stack);
                if( $open_tag != 'if' && $open_tag != 'elseif' )
                    $this->syntax_error('unexpected {else}');
                else
                    $this->push_tag('else');

                return S_PHP . ' else: ' . E_PHP;
            }

            case 'elseif':
            {
                list($open_tag) = end($this->tag_stack);
                if( $open_tag != 'if' && $open_tag != 'elseif' )
                    $this->syntax_error('unexpected {elseif}');
                if( $open_tag == 'if' )
                    $this->push_tag('elseif');

                return $this->compile_if_tag($tag['attributes'], true);
            }

            case '/if':
            {
                $this->pop_tag('if');
                return S_PHP . ' endif; ' . E_PHP;
            }

            case 'capture':
            {
                $this->push_tag('capture');
                return $this->compile_capture_tag(true, $tag['attributes']);
            }

            case '/capture':
            {
                $this->pop_tag('capture');
                return $this->compile_capture_tag(false);
            }

            case 'nocache':
            {
                $this->push_tag('nocache');
                $this->nocache_token = md5(uniqid(rand(), true));
                return $this->nocache_token . S_PHP . ' ob_start(); ' . E_PHP . "{nocache {$this->nocache_token}}";
            }

            case '/nocache':
            {
                $this->pop_tag('nocache');
                $serialized = base64_encode($this->nocache_buffer);
                return "{/nocache {$this->nocache_token}}" . S_PHP . " \$this->nocache['{$this->nocache_token}'] = ob_get_contents(); ob_end_clean(); " .
                       "\$this->codecache['{$this->nocache_token}'] = '{codecache {$this->nocache_token}}'; " . E_PHP;
            }

            case 'ldelim':
                return $this->left_delimiter;

            case 'rdelim':
                return $this->right_delimiter;

            case 'literal':
            {
                $this->push_tag('literal');
                return '';
            }

            case '/literal':
            {
                $this->pop_tag('literal');
                return '';
            }

            case 'foreach':
            {
                $this->push_tag('foreach');
                return $this->compile_foreach_start($tag['attributes']);
            }

            case 'foreachelse':
            {
                $this->push_tag('foreachelse');
                return S_PHP . ' endforeach; else: ' . E_PHP;
            }

            case '/foreach':
            {
                $open_tag = $this->pop_tag('foreach');
                if( $open_tag == 'foreachelse' )
                    return S_PHP . ' endif; ' . E_PHP;
                else
                    return S_PHP . ' endforeach; endif; ' . E_PHP;
            }

            case 'range':
            {
                $this->push_tag('range');
                return $this->compile_range_start($tag['attributes']);
            }

            case '/range':
            {
                $this->pop_tag('range');
                return S_PHP . ' endforeach; ' . E_PHP;
            }

            case 'php':
            {
                $this->push_tag('php');
                return S_PHP;
            }

            case '/php':
            {
                $this->pop_tag('php');
                return E_PHP;
            }

            case 'categories':
                return $this->compile_categories_tag($tag['attributes']);

            case 'news':
                return $this->compile_news_tag($tag['attributes']);

            case 'links':
                return $this->compile_links_tag($tag['attributes']);

            case 'file':
                return $this->compile_file_tag($tag['attributes']);

            case 'include':
                return $this->compile_include_tag($tag['attributes']);

            case 'cycle':
                return $this->compile_cycle_tag($tag['attributes']);

            case 'options':
                return $this->compile_options_tag($tag['attributes']);

            case 'assign':
                return $this->compile_assign_tag($tag['attributes']);

            case 'field':
                return $this->compile_field_tag($tag['attributes']);

            case 'ad':
                return $this->compile_ad_tag($tag['attributes']);

            case 'searches':
                return $this->compile_searches_tag($tag['attributes']);

            default:
            {
                // Return value unchanged
                return $matches[0];
            }
        }
    }

    function compile_searches_tag($tag_args)
    {
        global $DB;

        $defaults = array('amount' => 'all',
                          'order' => 'searches DESC',
                          'alphabetize' => 'false',
                          'minfont' => 80,
                          'maxfont' => 200);

        $attrs = $this->parse_attributes($tag_args);
        $attrs = array_merge($defaults, $attrs);

        if( empty($attrs['var']) )
            return $this->syntax_error("searches: missing 'var' attribute");

        if( !preg_match('~^\d+$~', $attrs['minfont']) )
            $attrs['minfont'] = $defaults['minfont'];

        if( !preg_match('~^\d+$~', $attrs['maxfont']) )
            $attrs['maxfont'] = $defaults['maxfont'];

        $attrs['var'] = $this->parse_vars($attrs['var']);
        $attrs['alphabetize'] = $this->to_bool($attrs['alphabetize']);

        $stats = new SelectBuilder('@min_searches:=MIN(`searches`),@max_searches:=MAX(`searches`)', 'lx_search_terms');
        $s = new SelectBuilder("*,ROUND((`searches`-@min_searches) * ({$attrs['maxfont']}-{$attrs['minfont']})/(@max_searches-@min_searches) + {$attrs['minfont']}) AS `font_size`", 'lx_search_terms');

        if( is_numeric($attrs['amount']) )
        {
            $s->SetLimit($attrs['amount']);
            $stats->SetLimit($attrs['amount']);
        }

        $fields = array('searches', 'term');
        $s->SetOrderString($attrs['order'], $fields);
        $stats->SetOrderString($attrs['order'], $fields);

        $query = $DB->Prepare($s->Generate(), $s->binds);
        $stats_query = $DB->Prepare($stats->Generate(), $stats->binds);

        return S_PHP . " \$GLOBALS['DB']->Query(\"$stats_query\");\n" .
               " {$attrs['var']} =& \$GLOBALS['DB']->FetchAll(\"$query\"); " . ($attrs['alphabetize'] ? " usort({$attrs['var']}, 'CompareSearches'); " : '') . E_PHP;
    }

    function compile_ad_tag($tag_args)
    {
        global $DB;

        $defaults = array('pagedupes' => 'false',
                          'weight' => 'any',
                          'order' => 'times_displayed, (unique_clicks/times_displayed) DESC');

        $attrs = $this->parse_attributes($tag_args);
        $attrs = array_merge($defaults, $attrs);

        // Convert boolean values
        $attrs['pagedupes'] = $this->to_bool($attrs['pagedupes']);

        // Prepare RAND() values in order
        $attrs['order'] = preg_replace('~rand\(\)~i', 'RAND(%RAND%)', $attrs['order']);

        $s = new SelectBuilder('*,`lx_ads`.`ad_id` AS `ad_id`', 'lx_ads');

        // Process pagedupes
        if( $attrs['pagedupes'] === FALSE )
        {
            $s->AddJoin('lx_ads', 'lx_ads_used_page', 'LEFT', 'ad_id');
            $s->AddWhere('lx_ads_used_page.ad_id', ST_NULL, null);
        }


        // Process tags attribute
        if( isset($attrs['tags']) )
        {
            $s->AddFulltextWhere('tags', $attrs['tags']);
        }


        // Process weight attribute
        if( isset($attrs['weight']) && $attrs['weight'] != 'any' )
        {
            $s->AddWhereString("`weight` {$attrs['weight']}");
        }

        $s->SetOrderString($attrs['order'], $DB->GetColumns('lx_ads'));
        $s->SetLimit('1');

        $query = $DB->Prepare($s->Generate(), $s->binds);


        // Perform replacements for placeholders
        $replacements = array('%RAND%' => '".rand()."');
        foreach($replacements as $find => $replace)
        {
            $query = str_replace($find, $replace, $query);
        }

        return S_PHP .
               " if( !isset(\$GLOBALS['_CLEAR_PAGE_USED_']) )\n{\n" .
               "\$GLOBALS['DB']->Update('DELETE FROM `lx_ads_used_page`');\n" .
               "\$GLOBALS['_CLEAR_PAGE_USED_'] = TRUE;\n" .
               "}\n" .
               "\$_temp_ad = \$GLOBALS['DB']->Row(\"$query\");\n" .
               "if( \$_temp_ad )\n{\n" .
               "\$GLOBALS['DB']->Update(\"UPDATE `lx_ads` SET `times_displayed`=`times_displayed`+1 WHERE `ad_id`=?\", array(\$_temp_ad['ad_id']));\n" .
               "\$GLOBALS['DB']->Update(\"REPLACE INTO `lx_ads_used_page` VALUES (?)\", array(\$_temp_ad['ad_id']));\n" .
               "echo \$_temp_ad['ad_html'];\n" .
               "}\n" .
               E_PHP;
    }

    function compile_field_tag($tag_args)
    {
        $attrs = $this->parse_attributes($tag_args);

        if( empty($attrs['from']) )
            return $this->syntax_error("field: missing 'from' attribute");

        if( empty($attrs['value']) )
            return $this->syntax_error("field: missing 'value' attribute");

        $from = $this->parse_vars($attrs['from']);
        $value = $this->parse_vars($attrs['value']);

        return S_PHP . " echo FormField($from, $value); " . E_PHP;
    }

    function compile_options_tag($tag_args)
    {
        $attrs = $this->parse_attributes($tag_args);

        if( empty($attrs['from']) )
            return $this->syntax_error("options: missing 'from' attribute");

        if( empty($attrs['key']) )
            return $this->syntax_error("options: missing 'key' attribute");

        if( empty($attrs['value']) )
            return $this->syntax_error("options: missing 'value' attribute");

        $from = $this->parse_vars($attrs['from']);
        $plain_key = $this->parse_vars(str_replace('$', '$_options_.', preg_replace('~\|.*~', '', $attrs['key'])));
        $key = $this->parse_vars(str_replace('$', '$_options_.', $attrs['key']));
        $value = $this->parse_vars(str_replace('$', '$_options_.', $attrs['value']));
        $selected = $this->parse_vars($attrs['selected']);

        return S_PHP . " foreach( $from as \$this->vars['_options_'] ): " . NEWLINE .
               "echo \"<option value=\\\"\" . $key .  \"\\\"" .
               (!empty($attrs['selected']) ? "\" . ($selected == $plain_key ? ' selected' : '') . \"" : '') .
               ">\" . $value . \"</option>\\n\";" . NEWLINE .
               'endforeach;  ' . E_PHP;
    }

    function compile_assign_tag($tag_args)
    {
        $attrs = $this->parse_attributes($tag_args);

        if( empty($attrs['var']) )
            return $this->syntax_error("assign: missing 'var' attribute");

        if( !isset($attrs['value']) )
            return $this->syntax_error("options: missing 'value' attribute");

        $var = $this->parse_vars($attrs['var']);
        $value = $this->parse_vars($attrs['value']);


        if( strpos($value, '$this->vars') === FALSE && !is_numeric($value) )
        {
            $value = '"' . $value . '"';
        }

        return S_PHP . " $var = $value; " . E_PHP;
    }

    function compile_cycle_tag($tag_args)
    {
        $attrs = $this->parse_attributes($tag_args);

        if( empty($attrs['values']) )
            return $this->syntax_error("cycle: missing 'values' attribute");

        list($first, $second) = explode(',', $attrs['values']);

        return S_PHP . " \$tmp_cycle = (\$tmp_cycle == '$first') ? '$second' : '$first'; echo \$tmp_cycle; " . E_PHP;
    }

    function compile_file_tag($tag_args)
    {
        $attrs = $this->parse_attributes($tag_args);

        if( empty($attrs['filename']) )
            return $this->syntax_error("file: missing 'filename' attribute");

        return S_PHP . " readfile('{$attrs['filename']}'); " . E_PHP;
    }

    function compile_include_tag($tag_args)
    {
        $attrs = $this->parse_attributes($tag_args);

        if( empty($attrs['filename']) )
            return $this->syntax_error("include: missing 'filename' attribute");

        if( !preg_match('~^global-~', $attrs['filename']) )
            return $this->syntax_error("include: only global templates can be included");

        if( !file_exists($this->compile_dir . '/' . $attrs['filename']) )
            return $this->syntax_error("include: compiled template '".$this->compile_dir . '/' . $attrs['filename']."' does not exist");

        return file_get_contents($this->compile_dir . '/' . $attrs['filename']);
    }

    function compile_if_tag($tag_args, $elseif = false)
    {
        // make sure we have balanced parenthesis
        $token_count = count_chars($tag_args);
        if( isset($token_count['(']) && $token_count['('] != $token_count[')'] )
            $this->syntax_error("unbalanced parenthesis in if statement");

        $tag_args = $this->parse_vars($tag_args);

        if( $elseif )
            return S_PHP . " elseif( $tag_args ): " . E_PHP;
        else
            return S_PHP . " if( $tag_args ): " . E_PHP;
    }

    function compile_news_tag($tag_args)
    {
        $attrs = $this->parse_attributes($tag_args);

        if( empty($attrs['var']) )
            return $this->syntax_error("news: missing 'var' attribute");

        if( empty($attrs['amount']) )
            return $this->syntax_error("news: missing 'amount' attribute");

        if( !preg_match('~^\w+$~', $attrs['var']) )
            return $this->syntax_error("'news: var' must be a variable name (literal string)");

        return S_PHP . " \$attrs = unserialize('" . serialize($attrs) . "');" . NEWLINE .
               "\$this->vars['{$attrs['var']}'] =& GetNews(\$attrs); " . E_PHP;
    }

    function compile_links_tag($tag_args)
    {
        $attrs = $this->parse_attributes($tag_args);

        if( empty($attrs['var']) )
            return $this->syntax_error("links: missing 'var' attribute");

        if( empty($attrs['type']) )
            return $this->syntax_error("links: missing 'type' attribute");

        if( !preg_match('~^\w+$~', $attrs['var']) )
            return $this->syntax_error("'links: var' must be a variable name (literal string)");

        $category = "\$this->vars['this_category']['category_id']";
        if( !empty($attrs['category']) )
        {
            if( is_numeric($attrs['category']) )
                $category = $attrs['category'];
            else
                $category = $this->parse_vars($attrs['category']) . "['category_id']";
        }

        $function = "GetLinksIn($category, \$attrs, \$this);";

        switch($attrs['type'])
        {
        case 'search':
            $function = "GetLinksSearch(\$attrs);";
            break;

        case 'new':
        case 'top':
        case 'popular':
            $function = "GetLinksBy(\$attrs);";
            break;
        }

        return S_PHP . " \$attrs = unserialize('" . serialize($attrs) . "');" . NEWLINE .
               "\$_links =& $function" . NEWLINE .
               "\$this->vars['{$attrs['var']}'] = \$_links['links'];" . NEWLINE .
               "if( \$_links['pagination'] !== FALSE ) \$this->vars['pagination'] = \$_links['pagination']; " . E_PHP;
    }

    function compile_categories_tag($tag_args)
    {
        $attrs = $this->parse_attributes($tag_args);

        if( empty($attrs['var']) )
            return $this->syntax_error("categories: missing 'var' attribute");

        if( !preg_match('~^\w+$~', $attrs['var']) )
            return $this->syntax_error("'categories: var' must be a variable name (literal string)");

        if( !isset($attrs['parent']) )
            $attrs['parent'] = 'this_category';

        if( isset($attrs['related']) )
        {
            $function = "GetCategoriesRelated(\$this->vars['{$attrs['parent']}']['related_ids']);";
        }
        else
        {
            $function = "GetCategoriesIn(\$this->vars['{$attrs['parent']}']['category_id'], '{$attrs['order']}', '{$attrs['amount']}');";
        }

        return S_PHP . " \$this->vars['{$attrs['var']}'] =& $function " . E_PHP;
    }

    function compile_range_start($tag_args)
    {
        $attrs = $this->parse_attributes($tag_args);

        if( empty($attrs['start']) )
            return $this->syntax_error("range: missing 'start' attribute");

        if( empty($attrs['end']) )
            return $this->syntax_error("range: missing 'end' attribute");

        if( empty($attrs['counter']) )
            return $this->syntax_error("range: missing 'counter' attribute");

        if( isset($attrs['counter']) && !preg_match('~^\w+$~', $attrs['counter']) )
            return $this->syntax_error("'range: counter' must be a variable name (literal string)");

        $attrs['start'] = $this->parse_vars($attrs['start']);
        $attrs['end'] = $this->parse_vars($attrs['end']);

        return S_PHP . " foreach( range({$attrs['start']}, {$attrs['end']}) as \$this->vars['{$attrs['counter']}']): " . E_PHP;
    }

    function compile_foreach_start($tag_args)
    {
        $attrs = $this->parse_attributes($tag_args);

        if( empty($attrs['from']) )
            return $this->syntax_error("foreach: missing 'from' attribute");

        if( empty($attrs['var']) )
            return $this->syntax_error("foreach: missing 'var' attribute");

        if( !preg_match('~^[$\w]+$~', $attrs['var']) )
            return $this->syntax_error("'foreach: var' must be a variable name (literal string)");

        if( isset($attrs['counter']) && !preg_match('~^[$\w]+$~', $attrs['counter']) )
            return $this->syntax_error("'foreach: counter' must be a variable name (literal string)");

        if( $attrs['from'] == $attrs['var'] )
            return $this->syntax_error("foreach: the 'var' and 'from' options cannot be set to the same value");

        if( strpos($attrs['var'], '$') !== 0 )
            $attrs['var'] = '$' . $attrs['var'];

        if( !empty($attrs['counter']) && strpos($attrs['counter'], '$') !== 0 )
            $attrs['counter'] = '$' . $attrs['counter'];

        $attrs['from'] = $this->parse_vars($attrs['from']);
        $attrs['var'] = $this->parse_vars($attrs['var']);
        $attrs['counter'] = $this->parse_vars($attrs['counter']);

        $key = null;
        $key_part = '';
        if( isset($attrs['key']) )
        {
            if( !preg_match('~^[$\w]+$~', $attrs['key']) )
                return $this->syntax_error("foreach: 'key' must to be a variable name (literal string)");

            $attrs['key'] = $this->parse_vars($attrs['key']);

            $key_part = "{$attrs['key']} => ";
        }

        $fromcount = ++$this->from_count;

        $output = S_PHP . " \$from$fromcount = {$attrs['from']};" . NEWLINE;
        $output .= "if( is_array(\$from$fromcount) ):" . NEWLINE;

        if( $attrs['counter'] )
            $output .= "    {$attrs['counter']} = 0;" . NEWLINE;

        $output .= "    foreach (\$from$fromcount as $key_part{$attrs['var']}):" . NEWLINE;

        if( $attrs['counter'] )
            $output .= "    {$attrs['counter']}++;" . NEWLINE;

        $output .= E_PHP;

        return $output;
    }

    function compile_capture_tag($start, $tag_args = '')
    {
        $attrs = $this->parse_attributes($tag_args);

        if( $start )
        {
            if( empty($attrs['name']) )
            {
                return $this->syntax_error("capture: missing 'name' attribute");
            }

            $name = $attrs['name'];
            $output = S_PHP . " ob_start(); " . E_PHP;
            $this->capture_stack[] = $name;
        }
        else
        {
            $name = array_pop($this->capture_stack);
            $output = S_PHP . " \$this->vars['CAPTURES']['$name'] = ob_get_contents(); ob_end_clean(); " . E_PHP;
        }

        return $output;
    }

    function syntax_error($error_msg)
    {
        $this->errors[] = "[line {$this->current_line}] $error_msg";
        $this->syntax_ok = FALSE;
    }

    function push_tag($tag)
    {
        array_push($this->tag_stack, array($tag, $this->current_line));
    }

    function pop_tag($tag)
    {
        if( count($this->tag_stack) > 0 )
        {
            list($open_tag, $line) = array_pop($this->tag_stack);

            if( $tag == $open_tag )
            {
                return $open_tag;
            }


            if( $tag == 'if' && ($open_tag == 'else' || $open_tag == 'elseif') )
            {
                return $this->pop_tag($tag);
            }


            if( $tag == 'foreach' && $open_tag == 'foreachelse' )
            {
                $this->pop_tag($tag);
                return $open_tag;
            }


            if( $open_tag == 'else' || $open_tag == 'elseif' )
            {
                $open_tag = 'if';
            }
            elseif( $open_tag == 'foreachelse' )
            {
                $open_tag = 'foreach';
            }

            $message = " expected {/$open_tag} (opened on line $line).";
        }

        $this->syntax_error("mismatched tag {/$tag}.$message");
    }

    function dequote($string)
    {
        if( (substr($string, 0, 1) == "'" || substr($string, 0, 1) == '"') && substr($string, -1) == substr($string, 0, 1) )
        {
            return substr($string, 1, -1);
        }
        else
        {
            return $string;
        }
    }

    function parse_tag($tag)
    {
        $parsed_tag = FALSE;

        if( $tag{0} == '$' )
        {
            $parsed_tag = array();
            $parsed_tag['tag'] = $tag;
            $parsed_tag['modifiers'] = '';
            $parsed_tag['attributes'] = '';

            // Check for tag modifiers
            if( preg_match('~([^|]+)\|(.*)$~s', $parsed_tag['tag'], $matches) )
            {
                $parsed_tag['tag'] = $matches[1];
                $parsed_tag['modifiers'] = $matches[2];
            }
        }
        else
        {
            // Separate the tag name from it's attributes
            if( preg_match('~([^\s]+)(\s+(.*))?$~s', $tag, $matches) )
            {
                $parsed_tag = array();
                $parsed_tag['tag'] = $matches[1];
                $parsed_tag['modifiers'] = '';
                $parsed_tag['attributes'] = $matches[3];
            }
        }

        return $parsed_tag;
    }

    function parse_attributes($attributes)
    {
        $parsed = array();

        if( preg_match_all('~([a-z_ ]+=.*?)(?=(?:\s+[a-z_]+\s*=)|$)~i', $attributes, $matches) )
        {
            foreach( $matches[1] as $match )
            {
                $equals = strpos($match, '=');
                $attr_name = $this->dequote(trim(substr($match, 0, $equals)));
                $attr_value = $this->dequote(trim(substr($match, $equals + 1)));

                $parsed[$attr_name] = $attr_value;
            }
        }

        return $parsed;
    }

    function parse_vars($input)
    {
        return preg_replace_callback('~(\$[a-z0-9._\[\]]+)(\|{1}.*)?~i', array(&$this, 'parse_vars_callback'), $input);
    }

    function parse_vars_callback($matches)
    {
        $variable = $matches[1];
        $modifiers = substr($matches[2], 1);

        $dot = strpos($variable, '.');
        $parsed_var = '';

        if( $dot !== FALSE )
        {
            $parsed_var = preg_replace('~\$([a-z0-9_]+)\.([a-z0-9_]+)~i', '$this->vars[\'\1\'][\'\2\']', $variable);
        }
        else
        {
            $parsed_var = preg_replace('~\$([a-z0-9_]+)~i', '$this->vars[\'\1\']', $variable);
        }


        // Process modifiers
        if( !empty($modifiers) )
        {
            foreach( explode('|', $modifiers) as $modifier )
            {
                if( preg_match('~^([a-z0-9_\->\$]+)(::)?(.*)$~i', $modifier, $grabbed) )
                {
                    $function = $grabbed[1];
                    $args = $grabbed[3];

                    if( $function == 'htmlspecialchars' && empty($args) )
                        $args = 'ENT_QUOTES';

                    $args = $this->parse_vars($args);

                    if( !empty($args) )
                        $args = ", " . preg_replace('~::~', ', ', $args);

                    $parsed_var = "$function($parsed_var$args)";
                }
            }
        }

        return $parsed_var;
    }

    function get_error_string()
    {
        return join("\n", $this->errors);
    }

    function to_bool($value)
    {
        if( is_numeric($value) )
        {
            if( $value == 0 )
            {
                return FALSE;
            }
            else
            {
                return TRUE;
            }
        }
        else if( preg_match('~^any$~i', $value) )
        {
            return 'any';
        }
        else if( preg_match('~^true$~i', $value) )
        {
            return TRUE;
        }
        else if( preg_match('~^false$~i', $value) )
        {
            return FALSE;
        }

        return FALSE;
    }
}


?>
