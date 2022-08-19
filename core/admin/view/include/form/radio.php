
<div class="col-lg-12 gn-block-pb">
    <div class="row pb-3 gn-block-style">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?>
            <span id="swap" class="gn-swap-style" style="display: none; float:right; cursor:pointer; color: #a8a7a7;"><i class="iicon-menu-open"></i></span>
            <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
        </label>
        <div class="col-lg-12">
           
            <?php foreach($this->foreignData[$row] as $key => $value) :?>
                <?php if(is_int($key)) :?>

                <div class="form-check">
                    <label class="form-check-label">
                        <input 
                        type="radio" 
                        class="form-check-input-styled jsElement__sortableBlock" 
                        name="<?=$row?>" 
                        <?php 
                            if (isset($this->data[$row]) && $this->data[$row] == $key) 
                                echo 'checked';
                            elseif(!isset($this->data[$row]) && $this->foreignData[$row]['default'] == $value) 
                                echo 'checked';?> 
                        value="<?=$key?>"data-fouc>
                        <?=$value?>
                    </label>
                </div>

                <?php endif; ?>   
            <?php endforeach?>        
        
        </div>
    </div>
</div>