<?php

namespace GraftPHP\Framework;

class View
{

    public static function render($template, $vars = null, $viewpath = null)
    {
        if ($vars) {
            extract($vars);
        }

        $thisViewPath = $viewpath ? $viewpath : GRAFT_CONFIG['ViewPath'];
        $path = $thisViewPath . str_replace('.', '/', $template) . '.php';

        if (!file_exists($path)) {
            dd("Template ($template) not found");
        }

        ob_start();
        include $path;
        $view_contents = ob_get_clean();

        // check for and insert any embed content
        preg_match_all("/\{embed:(.*)\}/", $view_contents, $embed_tags);
        if (count($embed_tags[0]) > 0) {
            foreach($embed_tags[0] as $index => $embed) {
                $embed_path = $thisViewPath . str_replace('.','/',$embed_tags[1][$index]) . '.php';
                ob_start();
                include($embed_path);
                $embed_contents = ob_get_clean();
                $view_contents = str_replace($embed, $embed_contents, $view_contents);
            }
        }

        // check for a template tag, we will only use the first one
        preg_match_all("/\{template:(.*)\}/", $view_contents, $template_tag);
        if(count($template_tag[0]) > 0) {
            $template_path = $thisViewPath . str_replace('.','/', $template_tag[1][0]) . '.php';
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
