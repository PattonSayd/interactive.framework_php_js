
<div class="content">

    <!-- State saving -->
    <div class="card">
        <table class="table datatable-save-state">
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Content</th>
                    <th>Table</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th class="text-center d-none">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($this->data) : ?>
			    <?php foreach ($this->data as $data) :?>
                <tr>
                    <td><a href="<?=$data['alias'] ?: $this->admin_path.'/edit/'.$data['table_name'].'/'.$data['alias']?>"><?=preg_replace('/\(.+?\)\s*$/i' , '', $data['name'])?></a></td>
                    <td><?=isset($data['content']) ? $data['content'] : ''?></td>
                    <td><?=isset($this->menu[$data['table_name']]) ? $this->menu[$data['table_name']]['name'] : $data['table_name']?></td>
                    <td><?=isset($data['created_at']) ? date('d.m.Y', strtotime($data['created_at'])) : ''?></td>
                    <td>
                    <?php if(isset($data['visible'])) :?>
                        <?php if($data['visible'] == 1) :?>
                            <span class="badge badge-success">Active</span></td>
                        <?php else :?>
                            <span class="badge badge-danger">Inactive</span></td>
                        <?php endif;?>
                    <?php else :?>
                        <span class="badge badge-secondary">Static</span></td>
                    <?php endif;?>
                    <td class="text-center d-none">
                        <div class="list-icons">
                            <div class="dropdown">
                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                    <i class="icon-menu9"></i>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="#" class="dropdown-item"><i class="icon-file-pdf"></i> Export to .pdf</a>
                                    <a href="#" class="dropdown-item"><i class="icon-file-excel"></i> Export to .csv</a>
                                    <a href="#" class="dropdown-item"><i class="icon-file-word"></i> Export to .doc</a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
		    <?php endif; ?>
     
            </tbody>
        </table>
    </div>
				<!-- /state saving -->
   
</div>


