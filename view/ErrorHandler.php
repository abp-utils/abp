<?php
use abp\component\Resource;
echo '<meta charset="utf-8">';
echo '<link rel="shortcut icon" type="image/x-icon" href="/resourse/img/logo.png">';
Resource::register([
    [
        'resource' => 'satisfy',
    ],
    [
        'resource' => 'bootstrap',
    ],
]);
?>
<div class="error-handler-header">
    <div class="error-handler-error-name"><?= $exceptionName ?></div>
    <div class="error-handler-error-message"><?= $exceptionText ?></div>
</div>
<div class="error-handler-body">
    <div class="error-handler-body-cont">
        <?php foreach ($trace as $key => $line) : ?>
        <div class="error-handler-body-cont-line">
            <div class="error-handler-body-cont-line-number"><?= $key + 1 ?>.</div>
            <div class="error-handler-body-cont-line-text"><?= $line['text'] ?></div>
            <div class="error-handler-body-cont-line-atline">at line</div>
            <div class="error-handler-body-cont-line-line"><?= $line['line'] ?></div>
            <div class="_clear"></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="error-handler-footer">
    <div class="error-handler-footer-get">
       <?php if (!empty($_GET)) {echo '<pre>';echo '$_GET= ';print_r($_GET); echo '</pre>';}?>
    </div>
    <div class="error-handler-footer-post">
       <?php if (!empty($_POST)) {echo '<pre>';echo '$_POST = ';print_r($_POST); echo '</pre>';}?>
    </div>
    <div class="error-handler-footer-cookie">
        <?php if (!empty($_COOKIE)) {echo '<pre>'; echo '$_COOKIE = ';print_r($_COOKIE); echo '</pre>';}?>
    </div>
    <div class="error-handler-footer-session">
        <?php if (!empty($_SESSION)) {echo '<pre>'; echo '$_SESSION = ';print_r($_SESSION); echo '</pre>';}?>
    </div>
    <div class="error-handler-footer-server">
        <?php if (!empty($_SERVER)) {echo '<pre>'; echo '$_SERVER = ';print_r($_SERVER); echo '</pre>';}?>
    </div>
</div>
<style>
    .error-handler-header, .error-handler-footer{
        width: 100%;
        min-width: 860px;
        margin: 0 auto;
        background: #eaeaea;
        padding: 40px 50px 30px 50px;
        border-bottom: #ccc 1px solid;
    }
    .error-handler-footer{
        border-top: #ccc 1px solid;
    }
    .error-handler-error-name{
        font-size: 30px;
        color: #f11c1c;
        margin-bottom: 30px;
    }
    .error-handler-error-message{
        font-size: 20px;
        line-height: 1.25;
        color: #505050;
    }
    .error-handler-body{
        margin-top: 30px;
        margin-bottom: 40px;
    }
    .error-handler-body-cont{
    }
    .error-handler-body-cont-line{
        background-color: #fafafa;
        padding: 10px 0;
        box-sizing: content-box;
    }
    .error-handler-body-cont-line-number,
    .error-handler-body-cont-line-text,
    .error-handler-body-cont-line-atline,
    .error-handler-body-cont-line-line{
        float: left;
    }
    .error-handler-body-cont-line-number{
        width: 6%;
        margin-left: 4%;
    }
    .error-handler-body-cont-line-text{
        width: 70%;
    }
    .error-handler-body-cont-line-atline{
        width: 8%;
    }
    .error-handler-body-cont-line-line{
        width: 7%;
        margin-right: 5%;
        text-align: right;
    }
    .error-handler-p{
        color: #fff;
    }
</style>
<script>
    var elems = document.getElementsByClassName('error-handler-body-cont-line-text');
    Array.prototype.forEach.call(elems, function(element) {
        var height = getComputedStyle(element).height;
        console.log(height);
        console.log(element);
        console.log(element.closest('.error-handler-body-cont-line').style.height = height);
    });
</script>
