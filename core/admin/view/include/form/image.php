
<div class="form-group col-lg-12">
    <div class="row">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?>
            <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
        </label>
        <div class="col-lg-12">

            <button type="button" 
                    class="btn" 
                    style="background-color: #f2f2f1; color: #677d8a; border: 1px solid #b7b7b7; border-bottom:3px solid #b5b5b5" 
                    onclick="document.getElementById('<?$row?>').click();"><i class="icon-folder-open3"></i>
            </button>
            <input type="file" style="display:none;" id="<?$row?>" name="image_file"/>

            <button type="button" 
                    class="btn btn-dark" 
                    style="background-color: #960c23; color: #ffe9fe; border: 1px solid #58131e; border-bottom:3px solid #680616" 
                    onclick="document.getElementById('image_delete').click();"><i class="icon-cross2"></i>
            </button>
            <a href="/" style="display:none;" id="image_delete"></a>

            <div class="d-flex align-items-center justify-content-center mt-1" style="width:400px; height:400px; border:2px dotted #8a8a8a" >
            <?php if ($this->data[$row]) : ?>
                <img src="<?=PATH . UPLOAD_DIR . $this->data[$row]?>" class="w-100" alt="Responsive image">
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>