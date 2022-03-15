
<div class="form-group col-lg-12">
    <div class="row">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?>
            <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
        </label>
        <div class="col-lg-12">
            <select class="form-control" name="<?=$row?>">
                <?php foreach($this->foreignData[$row] as $value) :?>
                    <option value="<?=$value['id']?>" <?=$this->data[$row] == $value['id'] ? 'selected' : '';?>>
                        <?=$value['name']?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>