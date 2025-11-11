<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to StudyAids</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= CSS_PATH ?>style.css">
    <style>
        .logo-box {
            background-color: #00000000;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 5px;
            font-size: 14px;
        }
        .logo-box img {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }
        .brand-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #212529;
        }
        .btn-login {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }
        .btn-login:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
            color: white;
        }
        .btn-signup {
            background-color: #e9ecef;
            border-color: #e9ecef;
            color: #212529;
        }
        .btn-signup:hover {
            background-color: #dee2e6;
            border-color: #dee2e6;
            color: #212529;
        }
        .upload-box {
            background-color: #e7d5ff;
            border-radius: 12px;
            padding: 80px 40px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .upload-box:hover {
            background-color: #d4b5ff;
        }
        .upload-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        .upload-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 10px;
        }
        .upload-formats {
            font-size: 0.9rem;
            color: #495057;
        }
        .welcome-heading {
            font-size: 2.5rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 15px;
        }
        .welcome-subheading {
            font-size: 1rem;
            color: #495057;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <div class="container-fluid px-4 py-3">
        <header class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="logo-box"><img src="<?= IMG_LOGO ?>" alt="StudyAids Logo"></div>
                <span class="brand-name">StudyAids</span>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-login rounded-pill px-4" href="<?php echo LOGIN; ?>" role="button">Login</a>
                <a class="btn btn-signup rounded-pill px-4" href="<?php echo REGISTER; ?>" role="button">Sign up</a>
            </div>
        </header>
    </div>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="welcome-heading">Welcome to StudyAids</h1>
                <p class="welcome-subheading">Just a few quick steps to get you started.</p>
                
                <div class="upload-box mt-4">
                    <div class="upload-icon">📄</div>
                    <div class="upload-title">Click or drag to upload document</div>
                    <div class="upload-formats">Supported formats: PDF, DOCS, PPTX, TXT</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>