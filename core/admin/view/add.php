<!-- Content area -->
<div class="content">

    <?php if(isset($_SESSION['res']['answer'])) :?>
    <div class="mx-auto">
        <?=$_SESSION['res']['answer']?>
    </div>
   <?php endif; ?>
    <!-- Form inputs -->
    <div class="card">
        <div class="card-body">

            <form action="<?=$this->adminPath . $this->action?>" method="post" class="form-validate-jquery" enctype="multipart/form-data">

                <div class="row mb-3">
                    <button type="submit" class="btn ml-2" style="background-color: #00b389; color:#c9fff5; border: 1px solid #10856b; border-bottom:3px solid #10856b">Success</button>
                <?php if(!$this->noDelete && $this->data) : ?>
                    <a href="<?=$this->adminPath . 'delete/' . $this->table . '/' . $this->data[$this->columns['primary_key']]?>"" class="btn ml-2" style="background-color: #fff9d9; color:#a68349; border: 1px solid #b79f5f; border-bottom:3px solid #c99c27">Delete</a>
                <?php endif; ?>
                </div>
                
                <fieldset class="mb-3 row">

                    <?php
                        foreach ($this->blocks as $class => $block) {

                            if($class === 'l-section' || $class === 'r-section') $col = 'col-lg-6';
                            else$col = 'col-lg-12';
                            
                            echo '<div class=" '. $col . '">';

                            if($block){

                                foreach ($block as $row){

                                    foreach ($this->templates as $template => $items) {

                                        if(in_array($row, $items)){

                                            if(!@include $_SERVER['DOCUMENT_ROOT'] . $this->formTemplates . $template . '.php'){
                                                throw new \core\base\exception\RouteException('Не найден шаблон ' . $_SERVER['DOCUMENT_ROOT'] . $this->formTemplates . $template . '.php');
                                                
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

                <div class="row flex-row-reverse">
                    <button type="submit" class="btn mr-2" style="background-color: #00b389; color:#c9fff5; border: 1px solid #10856b; border-bottom:3px solid #10856b">Success</button>
                <?php if(!$this->noDelete && $this->data) : ?>
                    <a href="<?=$this->adminPath . 'delete/' . $this->table . '/' . $this->data[$this->columns['primary_key']]?>"" class="btn mr-2" style="background-color: #fff9d9; color:#a68349; border: 1px solid #b79f5f; border-bottom:3px solid #c99c27">Delete</a>
                <?php endif; ?>
                </div>

                <?php if($this->data) : ?>
                    <input type="hidden" name="<?=$this->columns['primary_key'];?>" value="<?=$this->data[$this->columns['primary_key']];?>">
                <?php endif ?>

                <input type="hidden" name="table" value="<?=$this->table;?>">

            </form>
        </div>
    </div>
	<!-- /form inputs -->
	<?php if(isset($_SESSION['res']['answer'])) :?>
        <?php unset($_SESSION['res']);?>
   <?php endif; ?>
</div>

<!-- /content area -->

