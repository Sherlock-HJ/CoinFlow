<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <!-- 最新版本的 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">    <title>勋章规则</title>
<style>
    #app{
        width: 800px;
        margin: 0 auto ;

    }

</style>
</head>
<body>

<div class="container">

    <h5 class="text-primary"> 拥有的勋章系数增加 会获得更高阶的勋章 </h5>

    <div class="row">
        <div class="col-md-12 col-lg-6">
            <table class="table">
                <caption class="text-info">每100个项目，拥有勋章总数</caption>

                <thead>
                <tr>
                    <th>铁肩沐新勋章</th>
                    <th>数量</th>
                    <th>系数</th>
                </tr>
                </thead>


                <tbody>
                <?php

                foreach ($xzs as $xz){
                    echo '<tr><td>';
                    echo $xz['name'];
                    echo '</td><td>';
                    echo $xz['total'];
                    echo '</td><td>';
                    echo $xz['xs'];
                    echo '</td></tr>';
                }

                ?>


                </tbody>

            </table>

        </div>
        <div class="col-md-12 col-lg-6">
            <table class="table">
                <caption  class="text-info">每100个项目，拥有警示总数</caption>

                <thead>
                <tr>
                    <th>沐新警示</th>
                    <th>数量</th>
                    <th>系数</th>
                </tr>
                </thead>


                <tbody>
                <?php

                foreach ($sjs as $xz){
                    echo '<tr><td>';
                    echo $xz['name'];
                    echo '</td><td>';
                    echo $xz['total'];
                    echo '</td><td>';
                    echo $xz['xs'];
                    echo '</td></tr>';
                }

                ?>
                </tbody>

            </table>

        </div>
    </div>

</div>




</body>
</html>