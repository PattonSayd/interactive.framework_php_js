
<div class="col-lg-12 mb-1">
    <div class="row pb-3" style="background: #fff;border: 1px solid rgba(0,0,0,.125); box-shadow: 0 2px 5px 0 rgb(0 0 0 / 5%)">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?>
            <span style="float:right; cursor:pointer; color: #a8a7a7;"><i class="icon-move-alt1"></i></span>
            <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
        </label>
        <div class="col-lg-12">
           
            <?php foreach($this->foreignData[$row] as $key => $value) :?>
                <?php if(is_int($key)) :?>

                <div class="form-check">
                    <label class="form-check-label">
                        <input 
                        type="radio" 
                        class="form-check-input-styled" 
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