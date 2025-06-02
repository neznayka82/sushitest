<!DOCTYPE html>
<html>
<head>
    <title>Добавление кота</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-8"
            <div class="card">
                <div class="card-header">
                    <h4>Форма для добавления кота</h4>
                </div>
                <?php if (!empty($errors)) { ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error) { ?>
                            <p>Ошибка: <?= $error ?></p>
                        <?php } ?>
                    </div>
                <?php } ?>
                <div class="card-body">
                    <form id="catForm" method="POST" action="/cat/storage" >
                   <?php
                        include "_form.php";
                   ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
