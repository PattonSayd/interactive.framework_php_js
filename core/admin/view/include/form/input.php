
<div class="form-group col-lg-12">
    <div class="row">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?> <span class="text-danger">*</span>
            <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
        </label>
        <div class="col-lg-12">
            <input type="text"
                   name="<?=$row?>" 
                   class="form-control" 
                   required placeholder="Enter name..."
                   value="<?=isset($_SESSION['res'][$row]) ? 
                                htmlspecialchars($_SESSION['res'][$row]) : 
                                htmlspecialchars($this->data[$row])?>"
                   >
        </div>
    </div>
</div>  