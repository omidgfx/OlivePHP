<?php
/** @var Exception $exception
 * @var mixed $code
 */
?>
<!doctype html>
<html lang="en">
<head>
    <style>
        .olive-err {
            margin        : 20px;
            padding       : 20px;
            font-size     : 11px;
            border        : 1px solid #ddd;
            background    : #eee;
            line-height   : 1em;
            color         : #444;
            border-radius : 8px;
            font-family   : Consolas, monospace;
        }

        .olive-err h1 {
            font-family : 'Segoe UI', sans-serif;
            color       : #aaa;
            margin      : 0 0 8px 0;
            line-height : 1em;
            font-weight : 100;
            font-size   : 400%;
        }

        .olive-err small {
            font-family : 'Segoe UI', sans-serif;
            color       : #aaa;
            font-size   : 100%;
            font-weight : 400;
            text-align  : right;
            margin-top  : 16px;
            display     : block;
        }

        /*.olive-err p {
            font-family : Consolas, monospace;
            line-height : 1.3em;
            text-align  : justify;
            margin      : 0;
        }*/

        .olive-err hr {
            border      : none;
            height      : 1px;
            width       : 100%;
            line-height : 1em;
            background  : #ddd;
            box-shadow  : 0 1px 0 #fff;
        }

        .olive-err > ol {
            line-height   : 1.3em;
            list-style    : none;
            counter-reset : item;
            padding       : 0 0 0 40px;
        }

        .olive-err > ol > li:before {
            content       : counter(item);
            background    : #fafafa;
            border-radius : 5px;
            width         : 30px;
            height        : 30px;
            line-height   : 30px;
            font-weight   : bold;
            font-size     : 15px;
            color         : #aaa;
            text-align    : center;
            position      : absolute;
            left          : -40px;
            box-shadow    : 0 1px 2px rgba(0, 0, 0, .1);
        }

        .olive-err > ol > li {
            position          : relative;
            counter-increment : item;
            margin-bottom     : 5px;
            background        : #fff;
            border-radius     : 4px;
            box-shadow        : 0 1px 2px rgba(0, 0, 0, .1);
        }

        .olive-err > ol > li .olive-err-tracebody {
            padding : 4px 8px 8px 8px;
        }

        .olive-err > ol > li .olive-err-traceheading {
            font-weight   : bold;
            border-radius : 4px 4px 0 0;
            color         : cornflowerblue;
            border-bottom : 1px solid #f0f0f0;
            background    : #fafafa;
            padding       : 8px 8px 4px 8px;
        }

        .olive-err > ol > li .olive-err-traceheading a.s,
        .olive-err > ol > li .olive-err-traceheading a.g {
            vertical-align  : middle;
            display         : inline-block;
            text-decoration : none;
            opacity         : .5;
            margin          : -2px 4px 2px 0;
            padding         : 2px 4px;
            background      : cornflowerblue;
            float           : right;
            color           : white;
            border-radius   : 2px;
        }

        .olive-err > ol > li .olive-err-traceheading a:hover,
        .olive-err > ol > li .olive-err-traceheading a:focus {
            opacity : 1;
        }

        .olive-err > ol > li .olive-err-traceheading a.s:after,
        .olive-err > ol > li .olive-err-traceheading a.g:after {
            content        : 'Google';
            display        : inline-block;
            vertical-align : bottom;
        }

        .olive-err > ol > li .olive-err-traceheading a.s:after {content : 'Stack Overflow';}

        .olive-err > ol > li .olive-err-traceheading h2 {
            float         : right;
            margin        : -2px -2px 2px 0;
            background    : indianred;
            padding       : 2px 4px;
            font-size     : 100%;
            width         : auto;
            color         : #fff;
            display       : inline-block;
            border-radius : 2px;
        }

        .olive-err > ol > li .olive-err-traceheading h2:before {
            content : 'Error code ';
            color   : #fff;
            opacity : .4;
        }

        .olive-err > ol > li .olive-err-traceheading:after {
            content : '';
            display : block;
            clear   : both;
            width   : 100%;
        }

        .olive-err .olive-err-file {
            color         : #fff;
            margin        : 4px 0;
            display       : inline-block;
            background    : mediumslateblue;
            padding       : 2px 6px;
            font-size     : 120%;
            border-radius : 4px;
        }

        .olive-err > ol > li ol {
            padding       : 0;
            width         : 100%;
            margin        : 8px 0 0 0;
            border        : 1px solid rgba(0, 0, 0, .05);
            border-bottom : none;
            display       : table;
        }

        .olive-err > ol > li ol li {
            display : table-row;
        }

        .olive-err > ol > li ol li:nth-child(odd) {
            background : #fafafa;
        }

        .olive-err > ol > li ol li:after {
            display : block;
            width   : 100%;
            content : '';
        }

        .olive-err > ol > li ol code,
        .olive-err > ol > li ol span {
            padding       : 4px;
            border-bottom : 1px solid rgba(0, 0, 0, .05);
        }

        .olive-err > ol > li ol code {
            display     : table-cell;
            color       : #000;
            font-weight : bold;
            font-size   : 120%;
        }

        .olive-err > ol > li ol span {
            display : table-cell;

            /*float : right;*/
        }
    </style>
    <title>Error <?= $code ?></title>
</head>
<body>
<div class="olive-err">

    <h1>Error&nbsp;<?= $code ?></h1>
    <ol type="1">
        <?php
        /** @var Exception $e */
        $___er = function ($e) use (&$___er) {
            if ($e == null) return;

            ?>
            <li>
                <div class="olive-err-traceheading">
                    <?= ($msg = $e->getMessage()); ?>
                    <h2><?= (DEBUG_MODE ? get_class($e) . ' ' : null) . $e->getCode() ?></h2>
                    <?php if ($msg): ?>
                        <a class="g" href="https://www.google.com/search?q=<?= urlencode($msg) ?>" target="_blank"></a>
                        <a class="s" href="https://stackoverflow.com/search?q=<?= urlencode($msg) ?>" target="_blank"></a>
                    <?php endif; ?>
                </div>
                <div class="olive-err-tracebody">
                    <div class="olive-err-file"><?= $e->getFile() ?><span>:<?= $e->getLine() ?></span></div>

                    <?php
                    $trace = $e->getTrace(); ?>
                    <ol reversed>
                        <?php foreach ($trace as $t):
                            if (!isset($t['file'])) $t['file'] = 'Unkown file';
                            if (!isset($t['line'])) $t['line'] = '-1';
                            if (!isset($t['class'])) $t['class'] = '';
                            if (!isset($t['type'])) $t['type'] = '';
                            ?>
                            <li>
                                <code><?= $t['class'] . $t['type'] . $t['function'] ?>()</code>
                                <span><?= $t['file'] ?>:<?= $t['line'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </li>
            <?php $___er($e->getPrevious());
        };
        $___er($exception);
        ?>
    </ol>
    <hr>
    <small>Powered by&nbsp; <a href="https://github.com/omidgfx/OlivePHP">OlivePHP</a></small>
</div>
</body>
</html>
