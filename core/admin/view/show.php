
<!-- Content area -->
<div class="content">
	<?php if(isset($_SESSION['res']['answer'])) :?>
		<div class="gn-alert gn-hide" tabindex="-1">
			<?=$_SESSION['res']['answer']?>
			<?php unset($_SESSION['res']);?>			
		</div>
   <?php endif; ?>
   
	<div class="row mb-3">
		<div class="col-lg-2">
			<button type="button" onclick="window.location.href='<?=$this->admin_path?>add/<?=$this->table?>'" class="btn bg-teal-400 btn-labeled btn-labeled-left"><b><i class="icon-plus3"></i></b> Add</button>
		</div>			
	</div>
	<div class="row">
		<?php if ($this->data) : ?>
			<?php foreach ($this->data as $data) :?>
			<div class="col-lg-3 col-md-6 col-sm-12 mb-3">
				<div class="gn-block-style">
					<a href="<?=$this->admin_path?>edit/<?=$this->table?>/<?=$data['id']?>" style="color: #5a5e66">
						<div class="row">
							<div class="col-4 d-flex justify-content-center align-items-center">
								<div class="d-flex align-items-center pl-2" style="width:70px; height:70px;">
								<?php if($data['image']) : ?>
									<img src="<?=PATH . UPLOAD_DIR . $data['image']?>" alt="" style="object-fit: cover; max-width: 90%; max-height: 90%">
								<?php endif; ?>
								</div>
							</div>
							<div class="col-8 d-flex align-items-center justify-content-center">
								<div class="text-truncate px-3"><?=$data['name']?></div>
							</div>
						</div>
					</a>
				</div>
			</div>
			<?php endforeach; ?>
		<?php endif; ?>
		
	</div>
</div>
<!-- /content area -->

