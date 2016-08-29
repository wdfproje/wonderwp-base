<div class="compWrap"><span id="card" class="compTitle">card <span class="js-hideAll fa fa-eye"></span></span>
    <p class="compNotes"></p>
    <div class="component" style="background-color:"><?php include("../components/molecules/card.php"); ?></div>
    <div class="compCodeBox">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#card-markup" aria-controls="card-markup" role="tab"
                                                      data-toggle="tab">Markup</a></li>
            <li role="presentation"><a href="#card-css" aria-controls="card-css" role="tab" data-toggle="tab">scss</a>
            </li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active markup-display" id="card-markup"></div>
            <div role="tabpanel" class="tab-pane" id="card-css">
                <pre><code class="language-css"><?php include("../scss/molecules/_card.scss"); ?></code></pre>
            </div>
        </div>
    </div>
</div>