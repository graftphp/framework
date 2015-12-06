<?php

namespace GraftPHP\Framework;

class View
{

    public static function render($template, $vars = null) {
        if ($vars) {
            extract($vars);
        }

        $path = GRAFT_CONFIG['ViewPath'] . str_replace('.', '\\', $template) . '.php';

        if (!file_exists($path)) {
            dd("Template ($template) not found");
        }

        ob_start();
        include $path;
        $view_contents = ob_get_clean();

        // check for a template tag, we will only use the first one
        preg_match_all("/\{template:(.*)\}/", $view_contents, $template_tag);
        if(count($template_tag[0]) > 0) {
            $template_path = GRAFT_CONFIG['ViewPath'] . str_replace('.','\\', $template_tag[1][0]) . '.php';
            ob_start();
            include $template_path;
            $template_contents = ob_get_clean();

            // replace template regions with content from the child view
            preg_match_all("/\{(.*)\}/", $template_contents, $regions);
            foreach ($regions[1] as $region) {
                $start = '{' . $region . '}';
                $end = "{/$region}";
                $startpos = strpos($view_contents, $start);
                $endpos = strpos($view_contents, $end);
                $chunk = '';
                if ($startpos && $endpos) {
                    $startpos = $startpos+strlen($start)+1;
                    $chunk = substr($view_contents, $startpos, $endpos - $startpos);
                }
                $template_contents = str_replace($start, $chunk, $template_contents);
            }            
            $view_contents = $template_contents;
        }

        echo $view_contents;
        die();
    }

}

