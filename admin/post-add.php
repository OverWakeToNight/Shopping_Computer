<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	if(empty($_POST['post_title'])) {
		$valid = 0;
		$error_message .= 'Tiêu đề bài viết không thể trống<br>';
	} else {
		// Duplicate Checking
    	$statement = $pdo->prepare("SELECT * FROM tbl_post WHERE post_title=?");
    	$statement->execute(array($_POST['post_title']));
    	$total = $statement->rowCount();
    	if($total) {
    		$valid = 0;
        	$error_message .= "Tiêu đề bài đã tồn tại<br>";
    	}
	}

	if(empty($_POST['post_content'])) {
		$valid = 0;
		$error_message .= 'Đăng nội dung không thể trống<br>';
	}

	if(empty($_POST['post_date'])) {
		$valid = 0;
		$error_message .= 'Đăng xuất bản ngày không có sản phẩm nào<br>';
	}

	if(empty($_POST['category_id'])) {
		$valid = 0;
		$error_message .= 'Bạn phải chọn một danh mục<br>';
	}


	$path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];


    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'Bạn phải tải lên tệp JPG, JPEG, GIF hoặc PNG<br>';
        }
    }
	

	if($valid == 1) {

		// getting auto increment id for photo renaming
		$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_post'");
		$statement->execute();
		$result = $statement->fetchAll();
		foreach($result as $row) {
			$ai_id=$row[10];
		}

		if($_POST['post_slug'] == '') {
    		// generate slug
    		$temp_string = strtolower($_POST['post_title']);
    		$post_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	} else {
    		$temp_string = strtolower($_POST['post_slug']);
    		$post_slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $temp_string);
    	}

    	// if slug already exists, then rename it
		$statement = $pdo->prepare("SELECT * FROM tbl_post WHERE post_slug=?");
		$statement->execute(array($post_slug));
		$total = $statement->rowCount();
		if($total) {
			$post_slug = $post_slug.'-1';
		}

		if($path=='') {
			// When no photo will be selected
			$statement = $pdo->prepare("INSERT INTO tbl_post (post_title,post_slug,post_content,post_date,photo,category_id,total_view,meta_title,meta_keyword,meta_description) VALUES (?,?,?,?,?,?,?,?,?,?)");
			$statement->execute(array($_POST['post_title'],$post_slug,$_POST['post_content'],$_POST['post_date'],'',$_POST['category_id'],0,$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description']));
		} else {
    		// uploading the photo into the main location and giving it a final name
    		$final_name = 'post-'.$ai_id.'.'.$ext;
            move_uploaded_file( $path_tmp, '../assets/uploads/'.$final_name );

            $statement = $pdo->prepare("INSERT INTO tbl_post (post_title,post_slug,post_content,post_date,photo,category_id,total_view,meta_title,meta_keyword,meta_description) VALUES (?,?,?,?,?,?,?,?,?,?)");
			$statement->execute(array($_POST['post_title'],$post_slug,$_POST['post_content'],$_POST['post_date'],$final_name,$_POST['category_id'],0,$_POST['meta_title'],$_POST['meta_keyword'],$_POST['meta_description']));
		}
	
		$success_message = 'Bài đăng được thêm thành công!';
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Thêm bài viết</h1>
	</div>
	<div class="content-header-right">
		<a href="post.php" class="btn btn-primary btn-sm">Xem tất cả</a>
	</div>
</section>


<section class="content">

	<div class="row">
		<div class="col-md-12">

			<?php if($error_message): ?>
			<div class="callout callout-danger">
				<p>
				<?php echo $error_message; ?>
				</p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
				<p><?php echo $success_message; ?></p>
			</div>
			<?php endif; ?>

			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Tiêu đề bài viết <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="post_title" placeholder="Ví dụ: bài tiêu đề">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Đăng slug </label>
							<div class="col-sm-6">
								<input type="text" class="form-control" name="post_slug" placeholder="Ví dụ: tiêu đề sau">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Nội dung bài viết <span>*</span></label>
							<div class="col-sm-9">
								<textarea class="form-control" name="post_content" id="editor1"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Đăng ngày xuất bản <span>*</span></label>
							<div class="col-sm-2">
								<input type="text" class="form-control" name="post_date" id="datepicker" value="<?php echo date('d-m-Y'); ?>">(Định dạng: dd-mm-YY)
							</div>
						</div>
						<div class="form-group">
				            <label for="" class="col-sm-2 control-label">Hình nổi bật</label>
				            <div class="col-sm-6" style="padding-top:6px;">
				                <input type="file" name="photo">
				            </div>
				        </div>
						<div class="form-group">
				            <label for="" class="col-sm-2 control-label">Chọn danh mục <span>*</span></label>
				            <div class="col-sm-3">
				            	<select class="form-control select2" name="category_id">
				            		<option value="">Chọn một danh mục</option>
				            		<?php
						            	$i=0;
						            	$statement = $pdo->prepare("SELECT * FROM tbl_category ORDER BY category_name ASC");
						            	$statement->execute();
						            	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
						            	foreach ($result as $row) {
						            		?>
											<option value="<?php echo $row['category_id']; ?>"><?php echo $row['category_name']; ?></option>
						            		<?php
						            	}
					            	?>
				            	</select>
				            </div>
				        </div>
						<h3 class="seo-info">SEO Thông tin</h3>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Thẻ tiêu đề </label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="meta_title">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Thẻ từ khóa </label>
							<div class="col-sm-9">
								<input type="text" class="form-control" name="meta_keyword">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Thẻ mô tả </label>
							<div class="col-sm-9">
								<textarea class="form-control" name="meta_description" style="height:200px;"></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Chấp nhận</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>