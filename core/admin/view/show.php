
<!-- Content area -->
<div class="content">

	<div class="row mb-3">
		<div class="col-lg-2">
			<button type="button" onclick="window.location.href='<?=$this->adminPath?>add/<?=$this->table?>'" class="btn bg-teal-400 btn-labeled btn-labeled-left"><b><i class="icon-plus3"></i></b> Add</button>
		</div>			
	</div>

	<!-- State saving -->
	<div class="card">
		<table class="table datatable-colvis-state">
			<thead>
				<tr>
					<th>Id</th>
					<th>Name</th>
					<th>Job Title</th>
					<th>DOB</th>
					<th>Status</th>
					<th>Salary</th>
					<th>Edition</th>
					
				</tr>
			</thead>
			<tbody>

			<?php if ($this->data) : ?>
				<?php foreach ($this->data as $data) :?>
					<tr>
						<td><?=$data['id']?></td>
						<td><?=$data['name']?></td>
						<td>Traffic Court Referee</td>
						<td>22 Jun 1972</td>
						<td><span class="badge badge-success">Active</span></td>
						<td>$85,600</td>
						<td>
							<a href="<?=$this->adminPath?>edit/<?=$this->table?>/<?=$data['id']?>" class="badge badge-icon badge-success">
								<i class="icon-gear"></i>
							</a>
							<a href="<?=$this->adminPath?>delete/<?=$this->table?>/<?=$data['id']?>" class="badge badge-icon badge-danger">
								<i class="icon-cross"></i>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
				
			</tbody>
		</table>
	</div>
	<!-- /state saving -->
</div>
<!-- /content area -->

