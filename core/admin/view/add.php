<!-- Content area -->
<div class="content">
    <?php if(isset($_SESSION['res']['answer'])) :?>
        <div class="gn-alert gn-hide" tabindex="-1">
            <?=$_SESSION['res']['answer']?>
        </div>
   <?php endif; ?>
    <!-- Form inputs -->    
            <form id="add-form" action="<?=$this->admin_path . $this->action?>" method="post" class="form-validate-jquery" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-6">
                        <button type="submit" class="btn" style="background-color: #00b389; color:#f0fff0; border: 1px solid #10856b; border-bottom:3px solid #10856b">Save</button>
                    <?php if(!$this->noDelete && $this->data) : ?>
                        <a href="<?=$this->admin_path . 'delete/' . $this->table . '/' . $this->data[$this->columns['primary_key']]?>"" class="btn ml-2" style="background-color: #fff9d9; color:#a68349; border: 1px solid #b79f5f; border-bottom:3px solid #c99c27">Delete</a>
                    <?php endif; ?>
                    </div>

                    <div class="col-6" id="parentBlock">
                        <button id="changeBlokcs" type="submit" class="btn float-right" style="background-color: #f2f2f1; color: #677d8a; border: 1px solid #b7b7b7; border-bottom:3px solid #b5b5b5;">Change blocks</button>
                    </div>
                </div>
                
                <fieldset class="mb-3 row" id="fieldset">

                    <?php
                        foreach ($this->blocks as $class => $block) {

                            if($class === 'left' || $class === 'right') $col = 'col-lg-6 ' . $class;
                            else $col = 'col-lg-12 ' . $class;
                            
                            echo '<div class=" '. $col . '">';

                            if($block){

                                foreach ($block as $row){

                                    if($this->action === 'edit' && $row === 'password') continue;

                                    foreach ($this->templates as $template => $items) {

                                        if(in_array($row, $items)){

                                            if(!@include $_SERVER['DOCUMENT_ROOT'] . $this->formTemplatesPath . $template . '.php'){
                                                throw new \core\base\exception\RouteException('Не найден шаблон ' . $_SERVER['DOCUMENT_ROOT'] . $this->formTemplatesPath . $template . '.php');
                                                
                                            }

                                            break;
                                            
                                        }
                                    }         
                                }                               
                            } 
                            echo '</div>';                          
                        }
                    ?>               
                    
                </fieldset>

                <input type="hidden" name="table" value="<?=$this->table;?>">

                <div class="row">
                    <div class="col-12 text-right">
                        <button type="submit" class="btn" style="background-color: #00b389; color:#c9fff5; border: 1px solid #10856b; border-bottom:3px solid #10856b">Save</button>
                    <?php if(!$this->noDelete && $this->data) : ?>
                        <a href="<?=$this->admin_path . 'delete/' . $this->table . '/' . $this->data[$this->columns['primary_key']]?>"" class="btn ml-2" style="background-color: #fff9d9; color:#a68349; border: 1px solid #b79f5f; border-bottom:3px solid #c99c27">Delete</a>
                    <?php endif; ?>
                    </div>
                </div>

                <?php if($this->data) : ?>
                    <input id="table_id" type="hidden" name="<?=$this->columns['primary_key'];?>" value="<?=$this->data[$this->columns['primary_key']];?>">
                <?php endif ?>
            </form>
      
	<!-- /form inputs -->
	<?php if(isset($_SESSION['res']['answer'])) :?>
        <?php unset($_SESSION['res']);?>
   <?php endif; ?>
</div>

<!-- /content area -->

