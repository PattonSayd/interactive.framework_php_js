
<div class="col-lg-12 gn-block-pb">
    <div class="row pb-3 gn-block-style">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?> 
            <?php if(array_key_exists($row, $this->validation)):?>
                <?php if(!empty($this->validation[$row]['empty'])):?>
                    <span class="text-danger">*</span>
                <?php endif;?>
            <?php endif;?>
            <span id="swap" class="gn-swap-style" style="display: none; float:right; cursor:pointer; color: #a8a7a7;"><i class="icon-menu-open"></i></span>
            <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
        </label>
        <div class="col-lg-12">
            <input type="text"
                   id="<?=$row?>-focus" 
                   name="<?=$row?>" 
                   class="form-control jsElement__sortableBlock" 
                   placeholder="Enter name..."
                   value="<?=isset($_SESSION['res'][$row]) ? 
                                htmlspecialchars($_SESSION['res'][$row]) : 
                                htmlspecialchars($this->data[$row])?>">
        </div>
    </div>
</div>      