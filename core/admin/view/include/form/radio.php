
<div class="form-group col-lg-12">
    <div class="row">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?>
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