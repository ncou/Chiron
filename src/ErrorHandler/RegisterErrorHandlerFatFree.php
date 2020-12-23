<?php

declare(strict_types=1);

namespace Chiron\ErrorHandler;

use Chiron\Console\Console;
use Chiron\ErrorHandler\Exception\FatalErrorException;
use ErrorException;
use Exception;
use Symfony\Component\Console\Output\StreamOutput;
use Throwable;

// TODO : harmoniser les couleurs de bleu qui sont utilisées comme template dans cette page d'error handler :
//https://material-ui.com/customization/palette/
//https://www.color-hex.com/color-palette/100305
//https://www.color-hex.com/color-palette/81189
//https://digitalsynopsis.com/design/beautiful-color-palettes-combinations-schemes/
//https://colorhunt.co/palettes/blue
//https://graf1x.com/shades-of-blue-color-palette/

// TODO : utiliser plutot la librairy "rainbow.js" plutot que "highlight.js" => https://github.com/ccampbell/rainbow

// Minify CSS and JS : https://datayze.com/howto/minify-css-with-php      /       https://datayze.com/howto/minify-javascript-with-php

// https://github.com/ikkez/f3-falsum/blob/master/src/Falsum/Run.php

// TODO : utiliser un cdn avec uniquement la librairie hightlight configurée pour le PHP + PHP Template.
// TODO : voir si il faut aussi ajouter le parser du PHP Template (cad quand le php se trouve dans du HTML) en faisant un test d'un Throw Exception dans un fichier de template xxx.phtml

// TODO : utiliser un CDN pour le lien vers le mono-blue.css et supprimer le fichier dupliqué dans le répertoire "Resources". Il faudra déplacer la surcharge de ce style directement dans le fichier style.css

final class RegisterErrorHandlerFatFree
{


    public static function handleError(Throwable $exception) {

        //\Base $fw

        $resources=__DIR__.'/Resources/';

        // clear output buffer
        while(ob_get_level())
            ob_end_clean();

        // CSS files
        $monoblue=file_get_contents($resources.'css/mono-blue.css');
        $styles=file_get_contents($resources.'css/style.css');

        // JS Files
        //$jquery=file_get_contents($resources.'js/jquery.js');
        $main=file_get_contents($resources.'js/main.js');

/*
        $status=$fw->get('ERROR.status');
        $code=$fw->get('ERROR.code');
        $text=$fw->get('ERROR.text');
        $trace=$fw->get('ERROR.trace');
*/

        $status=666;//$fw->get('ERROR.status');
        $code=$exception->getCode(); // TODO : améliorer la récupération du code pour les erreurs de type ErrorException => https://github.com/filp/whoops/blob/master/src/Whoops/Handler/PrettyPageHandler.php#L322
        $text=$exception->getMessage();
        $trace=$exception->getTraceAsString();

/*
        if (!$fw->devoid('EXCEPTION',$exception)) {
            $text = get_class($exception).': '.$text;
        }
*/

        $text = get_class($exception).':'.$text;


        $trace = '[D:\xampp\htdocs\nano5\vendor\chiron\injector\src\Injector.php:244] call_user_func_array(Array, Array)'. PHP_EOL;
        $trace .= '[D:\xampp\htdocs\nano5\vendor\chiron\injector\src\Injector.php:210] Chiron\Injector\Injector->invoke(Array, Array)'. PHP_EOL;
        $trace .= '[D:\xampp\htdocs\nano5\vendor\chiron\container\src\Container.php:393] Chiron\Injector\Injector->call(Array, Array)'. PHP_EOL;
        $trace .= '[D:\xampp\htdocs\nano5\vendor\chiron\http\src\CallableHandler.php:72] Chiron\Container\Container->call(Array, Array)'. PHP_EOL;
        $trace .= '[D:\xampp\htdocs\nano5\vendor\chiron\http\src\CallableHandler.php:58] Chiron\Http\CallableHandler->call(Array, Array)'. PHP_EOL;
        $trace .= '[D:\xampp\htdocs\nano5\vendor\chiron\pipeline\src\Pipeline.php:97] Chiron\Http\CallableHandler->handle(Object(Nyholm\Psr7\ServerRequest))'. PHP_EOL;
        $trace .= '[D:\xampp\htdocs\nano5\app\Middlewares\MiddlewareOne.php:28] Chiron\Pipeline\Pipeline->handle(Object(Nyholm\Psr7\ServerRequest))'. PHP_EOL;
        $trace .= '[D:\xampp\htdocs\nano5\vendor\chiron\pipeline\src\Pipeline.php:94] Middlewares\MiddlewareOne->process(Object(Nyholm\Psr7\ServerRequest), Object(Chiron\Pipeline\Pipeline))'.PHP_EOL;
        $trace .= '[D:\xampp\htdocs\nano5\vendor\chiron\pipeline\src\Pipeline.php:94] Middlewares\MiddlewareOne->process(Object(Nyholm\Psr7\ServerRequest), Object(Chiron\Pipeline\Pipeline))'.PHP_EOL;
        $trace .= '[D:\xampp\htdocs\nano5\vendor\chiron\pipeline\src\Pipeline.php:94] Middlewares\MiddlewareOne->process(Object(Nyholm\Psr7\ServerRequest), Object(Chiron\Pipeline\Pipeline))'.PHP_EOL;
        $trace .= '[D:\xampp\htdocs\nano5\vendor\chiron\pipeline\src\Pipeline.php:94] Middlewares\MiddlewareOne->process(Object(Nyholm\Psr7\ServerRequest), Object(Chiron\Pipeline\Pipeline))'.PHP_EOL;
        $trace .= '[D:\xampp\htdocs\nano5\vendor\chiron\pipeline\src\Pipeline.php:94] Middlewares\MiddlewareOne->process(Object(Nyholm\Psr7\ServerRequest), Object(Chiron\Pipeline\Pipeline))'.PHP_EOL;
        $trace .= '[D:\xampp\htdocs\nano5\vendor\chiron\pipeline\src\Pipeline.php:94] Middlewares\MiddlewareOne->process(Object(Nyholm\Psr7\ServerRequest), Object(Chiron\Pipeline\Pipeline))'.PHP_EOL;
        $trace .= '[D:\xampp\htdocs\nano5\public\index.php:73] Chiron\Application->run()'.PHP_EOL;



        preg_match_all("/\[.*:\d+\]/",strip_tags($trace),$matches);

        //die(var_dump($trace));

        if (!$exception)
            // drop first item, which is the error handler definition line
            if (!empty($matches[0]) && count($matches[0])>1)
                array_shift($matches[0]);

        $errors=[];

        foreach ($matches[0] as $key=>$result) {
            $result=str_replace(['[',']'],'',$result);
            preg_match_all("/:\d+/",$result,$line);
            if (!isset($errors[$key]))
                $errors[$key]=[];
            $errors[$key]['line']= str_replace(':','',$line[0][0]);
            // TODO : améliorer la gestion du "eval" => https://github.com/filp/whoops/blob/23c4a644caf876f91ae0d900a59b78b42220c947/src/Whoops/Exception/Frame.php#L58
            $errors[$key]['file']= preg_replace("/(:".$errors[$key]['line']."|\(\d+\) : eval\(\)\'d code:".$errors[$key]['line'].")/",'',$result);

            $eol='';
            $line=$errors[$key]['line']-1;
            $line_start=$line-10;
            $line_end=$line+10;

            //$path = $fw->get('ROOT').'/';
            $path = '';

            $rows=file(realpath($path.$errors[$key]['file']));
            $errors[$key]['script']='<div class="code-wrap">';
            $errors[$key]['script'].='<pre class="excerpt">'.$eol;
            for ($pos=$line_start;$pos<=$line_end;$pos++) {
                $row=isset($rows[$pos])?$rows[$pos]:'';
                if ($pos==$line) {
                    $errors[$key]['script'].='<code class="error-line">'.$pos.' '.htmlentities($row).'</code>'.$eol;
                } else
                    $errors[$key]['script'].='<code>'.$pos.' '.htmlentities($row).'</code>'.$eol;
            }
            $errors[$key]['script'].='</pre></div>';
        }

        $html_structure=''.
            '<html>'.
            '   <head>'.
            '       <style>'.$styles.'</style>'.
            '       <style>'.$monoblue.'</style>'.
            //'       <link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.4.1/styles/mono-blue.min.css">'.

            //'       <script type="text/javascript">'.$jquery.'</script>'.
            //'       <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.1.0/highlight.min.js"></script>'.
            '       <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.4.1/highlight.min.js"></script>'.
            '   </head>'.
            '   <body>'.
            '       <div id="container">'.
            '           <div class="header">'.
            '               <h1>'.$code.' '.$status.'</h1>'.
            '               <h2>'.$text.'</h2>'.
            '           </div>'.
            '           <div class="content">'.
            '               <div class="left"><div>'.
            '                   <h3>Code Analysis</h3>';

        foreach ($errors as $key=>$error) {
            $selected=$key==0?' selected':'';
            $html_structure.=''.'<div class="code'.$selected.'" ref="'.$key.'">'.$error['script'].'</div>';
        }

        $html_structure.='<h3 class="headers">Headers</h3>';


        $headers = [
                'Host' => 'chironframework.com',
                'Accept-Encoding' => 'gzip,deflate,sdch',
                'Accept-Language' => 'en-US,en;q=0.8,ja;q=0.6',
                'Host1' => 'chironframework.com',
                'Accept-Encoding1' => 'gzip,deflate,sdch',
                'Accept-Language1' => 'en-US,en;q=0.8,ja;q=0.6',
                'Host2' => 'chironframework.com',
                'Accept-Encoding2' => 'gzip,deflate,sdch',
                'Accept-Language2' => 'en-US,en;q=0.8,ja;q=0.6',
                'Host3' => 'chironframework.com',
                'Accept-Encoding3' => 'gzip,deflate,sdch',
                'Accept-Language3' => 'en-US,en;q=0.8,ja;q=0.6',
                'Host4' => 'chironframework.com',
                'Accept-Encoding4' => 'gzip,deflate,sdch',
                'Accept-Language4' => 'en-US,en;q=0.8,ja;q=0.6',
                'Host5' => 'chironframework.com',
                'Accept-Encoding5' => 'gzip,deflate,sdch',
                'Accept-Language5' => 'en-US,en;q=0.8,ja;q=0.6',
                'Host6' => 'chironframework.com',
                'Accept-Encoding6' => 'gzip,deflate,sdch',
                'Accept-Language6' => 'en-US,en;q=0.8,ja;q=0.6',
                'Host7' => 'chironframework.com',
                'Accept-Encoding7' => 'gzip,deflate,sdch',
                'Accept-Language7' => 'en-US,en;q=0.8,ja;q=0.6',
                'Host8' => 'chironframework.com',
                'Accept-Encoding8' => 'gzip,deflate,sdch',
                'Accept-Language8' => 'en-US,en;q=0.8,ja;q=0.6',
            ];
        //foreach ($fw->get('HEADERS') as $key=>$value) {
        foreach ($headers as $key=>$value) {
            $html_structure.='<div class="variables"><span>'.$key.'</span> '.
                $value.'</div>';
        }


        $html_structure.=
            '               </div></div>'.
            '               <div class="right"><div>'.
            '                   <h3>Error Stack</h3><div class="stacks">';

        foreach ($errors as $key=>$error) {
            $selected=$key==0?' selected':'';

            //$path=substr($error['file'],-50);
            $path = self::shortenPath($error['file']);

            $html_structure.=''.
                '<div class="stack'.$selected.'" ref="'.$key.'">'.
                '   <h4><span class="pos">'.$key.'</span> Line Number '.($error['line']-1).'</h4>'.
                //'   <p>...'.$path.'</p>'.
                '   <p>'.$path.'</p>'.
                '</div>';
        }

        $html_structure.=
            '               </div></div></div>'.
            '           </div>'.
            '       </div>'.
            '       <script type="text/javascript">'.$main.'</script>'.
            '   </body>'.
            '</html>';

        echo $html_structure;
    }

    private static function shortenPath(string $file): string
    {
        // Replace the part of the path that all frames have in common, and add 'soft hyphens' for smoother line-breaks.
        $dirname = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
        if ($dirname !== '/') { // TODO : attention dans le cas de windows cela ne fonctionnera pas car le séparateur de répertoires n'est pas "/" mais "\" !!! utiliser la constante DIRECTORY_SEPARATOR ????
            $file = str_replace($dirname, "&hellip;", $file);
        }
        $file = str_replace("/", "/&shy;", $file); // TODO : attention dans le cas de windows cela ne fonctionnera pas car le séparateur de répertoires n'est pas "/" mais "\" !!! utiliser la constante DIRECTORY_SEPARATOR ????

        return $file;
    }
}
