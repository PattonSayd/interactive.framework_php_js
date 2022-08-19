
<div class="col-lg-12 img-wrapper gn-block-pb">
    <div class="row pb-3" style="background:#fff;border:1px solid rgba(0,0,0,.125);box-shadow:0 2px 5px 0 rgb(0 0 0 / 5%)">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?>
            <span id="swap" class="gn-swap-style" style="display: none; float:right; cursor:pointer; color: #a8a7a7;"><i class="icon-menu-open"></i></span>
            <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
        </label>
        <div class="col-lg-12 d-flex flex-wrap gallery-container">
            <div class="d-flex align-items-center justify-content-center mb-1 mr-1 button-div gn-dotted-square">
                <button type="button" class="btn btn-light w-100 h-100" onclick="document.getElementById('<?=$row?>').click();">
                    <i class="icon-plus2" style="font-size:60px;"></i>
                </button>
                <input class="jsElement__sortableBlock" type="file" style="display:none;" id="<?=$row?>" name="<?=$row?>[]" multiple/>
            </div>

            <?php if ($this->data[$row]) :?>

                <?php $this->data[$row] = json_decode($this->data[$row]);?>

                <?php foreach ($this->data[$row] as $value) :?>

                    <a href="<?=$this->admin_path . 'delete/' .  $this->table . '/' .  
                                $this->data[$this->columns['primary_key']] .
                                '/' . $row . '/' . base64_encode($value)?>"
                                class="d-flex align-items-center justify-content-center gn-dotted-square mb-1 mr-1">
                        <img src="<?=PATH . UPLOAD_DIR . $value?>" class="gn-img-size" alt="...">  
                    </a>   
                    
                <?php endforeach; ?>

                <?php for($i=0; $i < 2; $i++) { ?>
                    <div class="d-flex align-items-center justify-content-center mb-1 mr-1 empty-container gn-dotted-square"></div>
                <?php } ?>
                    
            <?php else: ?>

                <?php for($i=0; $i < 9; $i++) { ?>
                    <div class="d-flex align-items-center justify-content-center mb-1 mr-1 empty-container gn-dotted-square"></div>
                <?php } ?>

            <?php endif; ?>
        </div>
    </div>
</div>