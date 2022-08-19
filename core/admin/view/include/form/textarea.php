
<div class="col-lg-12 gn-block-pb">
    <div class="row pb-3 element-tinyMCE gn-block-style">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?>
            <span id="swap" class="gn-swap-style" style="display: none; float:right; cursor:pointer; color: #a8a7a7;"><i class="icon-menu-open"></i></span>
            <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
        </label>
               <div class="col-lg-12 mb-1">
                    <div class="form-check">
                        <label class="form-check-label" style="color:#a8a7a7">
                            <input 
                            type="checkbox" 
                            class="form-check-input-styled input-tinyMCE input__sortableBlock jsElement__sortableBlock" 
                            <?=$class === 'center' ? 'checked' : '';?>
                            >
                            Tiny MCE
                        </label>
                    </div>
               </div>
        <div class="col-lg-12 textarea-tinyMCE">
            <textarea name="<?=$row?>" rows="5" cols="5" class="form-control" placeholder="Textarea"><?=isset($_SESSION['res'][$row]) ? 
                                htmlspecialchars($_SESSION['res'][$row]) : 
                                htmlspecialchars($this->data[$row])?></textarea>
        </div>
    </div>
</div>