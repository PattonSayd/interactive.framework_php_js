<div class="form-group col-lg-12">
    <div class="row">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?>
            <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
        </label>
        <?php if($this->foreignData[$row]) :?>
                <?php foreach($this->foreignData[$row] as $name => $value) :?>
                    <?php if($value['sub']) :?>
                        <div class="d-block w-100 mb-2" style="cursor: pointer">
                            <div class="col-lg-12">
                                <div class="borde form-control d-flex justify-content-between">
                                    <span><?=$value['name']?></span>
                                    <span>Select all <i class="icon-arrow-down22"></i></span>
                                </div>
                            </div>
                            <div class="col-lg-12" style="margin-top: 0px">
                                <div style="padding: 4px 0; border: 1px solid rgba(0,0,0,.15); border-top: 0px; box-shadow: 0 0.25rem 0.5rem rgb(0 0 0 / 10%);" >
                                    <?php foreach($value['sub'] as $item) :?>
                                        <div class="form-check my-1 ml-2">
                                            <label class="form-check-label" for="<?=$name?>-<?=$item['id']?>">
                                                <input type="checkbox" 
                                                    class="form-check-input-styled" 
                                                    id="<?=$name?>-<?=$item['id']?>"
                                                    value="<?=$item['id']?>" 
                                                    name="<?=$row?>[<?=$name?>][]"
                                                    data-fouc
                                                    <?php if(isset($this->data)){
                                                        if(in_array($item['id'], $this->data[$row][$name])) 
                                                            echo 'checked';
                                                        }?>>
                                                <?=$item['name']?>
                                            </label>
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