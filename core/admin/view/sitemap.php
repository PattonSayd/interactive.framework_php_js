<div class="content">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Sitemap</h5>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-8">
                    <div class="mb-3">
                    <?php if($this->xml) :?>
                        <h6 class="font-weight-semibold">Alias</h6>
                        <ul class="list list-unstyled">
                            <?php foreach ($this->xml->url as $url) :?>
                                <li><a href="<?=$url->loc?>"><?=$url->loc?></a></li>
                            <?php endforeach; ?>
                        
                        <?php else  :?>
                            <h1>Create a sitemap</h1>
                        </ul>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
			