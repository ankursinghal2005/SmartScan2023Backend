<!DOCTYPE html>
<html>
<head>
<title>OG Hackathon</title>
<meta content="noindex, nofollow" name="robots">
<link href='css/style.css' rel='stylesheet' type='text/css'>
</head>
<body>
<div class="main">
    <div class="first">
    <h2>S3 File Upload</h2>
    <?php if(!empty($_GET['msg'])){ ?>
        <h4><?php echo $_GET['msg'] ?><h4>
        <strong>File Name : </strong><?php echo $_GET['file_name'] ?>  
    </br>
    <?php }?>
    <form method="post" action="upload.php" enctype="multipart/form-data">
        <div class="form-group">
            <label><b>Select File:</b></label>
            <input type="file" name="userfile" class="form-control" required>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" name="submit" value="Upload">
        </div>
    </form>
    </div>
</div>
</body>
</html>
