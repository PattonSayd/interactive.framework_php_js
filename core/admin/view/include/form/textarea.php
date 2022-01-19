
<div class="form-group col-lg-12">
    <div class="row">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?>
            <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
        </label>
        <div class="col-lg-12">
            <textarea name="<?=$row?>" rows="5" cols="5" class="form-control" placeholder="Textarea"><?=isset($_SESSION['res'][$row]) ? 
                                htmlspecialchars($_SESSION['res'][$row]) : 
                                htmlspecialchars($this->data[$row])?></textarea>
        </div>
    </div>
</div>