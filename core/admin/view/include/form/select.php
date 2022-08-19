
<div class="col-lg-12 gn-block-pb">
    <div class="row pb-3 gn-block-style">
        <label class="col-form-label col-lg-12"><?=$this->translate[$row][0] ?: $row?>
            <span id="swap" class="gn-swap-style" style="display: none; float:right; cursor:pointer; color: #a8a7a7;"><i class="icon-menu-open"></i></span>
            <span class="d-block font-weight-light text-secondary"><?=$this->translate[$row][1]?></span>
        </label>
        <div class="col-lg-12">
            <select class="form-control form-control-uniform jsElement__sortableBlock" name="<?=$row?>" data-fouc>
                <?php foreach($this->foreignData[$row] as $value) :?>
                    <option value="<?=$value['id']?>" <?=$this->data[$row] == $value['id'] ? 'selected' : '';?>>
                        <?=$value['name']?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>
