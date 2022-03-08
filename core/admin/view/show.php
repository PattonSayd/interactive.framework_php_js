
<!-- Content area -->
<div class="content">
	<div class="row mb-3">
		<div class="col-lg-2">
			<button type="button" onclick="window.location.href='<?=$this->adminPath?>add/<?=$this->table?>'" class="btn bg-teal-400 btn-labeled btn-labeled-left"><b><i class="icon-plus3"></i></b> Add</button>
		</div>			
	</div>
	<!-- style="border: 1px solid rgba(0,0,0,.125); box-shadow: 0 1px 2px rgb(0 0 0 / 5%); -->
	<div class="row">

		<?php if ($this->data) : ?>
			<?php foreach ($this->data as $data) :?>
			<div class="col-lg-3 col-md-6 col-sm-12 mb-3">
				<div class="bg-white" style="border: 1px solid rgba(0,0,0,.125); box-shadow: 0 1px 2px rgb(0 0 0 / 5%);">
					<a href="<?=$this->adminPath?>edit/<?=$this->table?>/<?=$data['id']?>" style="color: #5a5e66">
						<div class="row">
							<div class="col-4 d-flex justify-content-center align-items-center">
								<div class="d-flex align-items-center" style="width:60px; height:60px;">
								<?php if($data['image']) : ?>
									<img src="<?=PATH . UPLOAD_DIR . $data['image']?>" alt="" class="w-100">
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

