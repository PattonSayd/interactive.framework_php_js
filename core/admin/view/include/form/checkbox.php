<div class="col-lg-12 mb-1">
    <div class="row pb-3" style="background: #fff;border: 1px solid rgba(0,0,0,.125); box-shadow: 0 2px 5px 0 rgb(0 0 0 / 5%)">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?>
            <span style="float:right;cursor:pointer; color: #a8a7a7;"><i class="icon-move-alt1"></i></span>
            <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
        </label>
        <?php if($this->foreignData[$row]) :?>
                <?php foreach($this->foreignData[$row] as $name => $value) :?>
                    <?php if($value['sub']) :?>
                        <div class="d-block w-100 mb-2 checkbox-container">
                            <div class="col-lg-12 select-wrap" style="cursor: pointer">
                                <div class="borde form-control d-flex justify-content-between">
                                    <span><?=$value['name']?></span>
                                    <span class="select-all">Select all <i class="icon-arrow-down22"></i></span>
                                </div>
                            </div>
                            <div class="col-lg-12 option-wrap" style="margin-top: 0px; display: none">
                                <div style="border: 1px solid rgba(0,0,0,.15); border-bottom: 4.4px solid rgba(0,0,0,.15);border-top: 0px; box-shadow: 0 0.25rem 0.5rem rgb(0 0 0 / 10%);" >
                                    <?php foreach($value['sub'] as $item) :?>
                                        <div class="gn-checkbox">
                                            <input type="checkbox" 
                                                   class="checked-box"
                                                   id="<?=$name?>-<?=$item['id']?>" 
                                                   name="<?=$row?>[<?=$name?>][]" 
                                                   value="<?=$item['id']?>"
                                                   <?php 
                                                    if(isset($this->data)){
                                                        if(empty($this->data[$row][$name])) echo '';
                                                        elseif(in_array($item['id'], $this->data[$row][$name])) 
                                                            echo 'checked';
                                                        }
                                                    ?>>
                                            <label style="cursor: pointer" class="lte" for="<?=$name?>-<?=$item['id']?>"><span></span><?=$item['name']?></label>
                                        </div>
                                    <?php endforeach;?>
                                </div>
                            </div>
                        </div>
                    <?php endif;?>
            <?php endforeach;?>
        <?php endif;?>
        
        

        
    </div>
</div>

