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
    body {background: transparent;}
</style>