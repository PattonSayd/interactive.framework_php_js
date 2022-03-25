
<div class="col-lg-12 mb-1 ">
    <div class="row pb-3 element-tinyMCE" style="background: #fff;border: 1px solid rgba(0,0,0,.125); box-shadow: 0 2px 5px 0 rgb(0 0 0 / 5%)">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?>
            <span style="float:right;cursor:pointer; color: #a8a7a7;"><i class="icon-move-alt1"></i></span>
            <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
        </label>
               <div class="col-lg-12 mb-1">
                    <div class="form-check">
                        <label class="form-check-label">
                            <input 
                            type="checkbox" 
                            class="form-check-input-styled input-tinyMCE"
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