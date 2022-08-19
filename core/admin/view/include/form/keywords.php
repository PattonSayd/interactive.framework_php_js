
<div class="col-lg-12 gn-block-pb">
    <div class="row pb-3 gn-block-style">
            <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?>
                <span id="swap" class="gn-swap-style" style="display: none; float:right; cursor:pointer; color: #a8a7a7;"><i class="icon-menu-open"></i></span>
                <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
            </label>
            <div class="col-lg-12">
                <input 
                    type="text" 
                    name="<?=$row?>" 
                    class="form-control tags-input jsElement__sortableBlock" 
                    value="<?=isset($_SESSION['res'][$row]) ? 
                                htmlspecialchars($_SESSION['res'][$row]) : 
                                htmlspecialchars($this->data[$row])?>" 
                    placeholder="Tags..." data-fouc>
            </div>
    </div>
</div>