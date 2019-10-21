<?php
date_default_timezone_set("Asia/Kolkata");
$link = mysqli_connect("localhost", "root", "", "searchdb");
// Check connection
if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}

if (!empty($_POST)) {
    foreach ($_POST as $key => $value) {
        $_POST[$key] = is_array($key) ? $_POST[$key]: strip_tags($_POST[$key], '');
    }
}


/** AJAX SCRIPTS */

// Insert term into database
if (array_key_exists('searchTerm', $_POST) and $_POST['searchTerm']!='' and array_key_exists('submit', $_POST)) {
    $query = "INSERT INTO `searches` (`searchTerm`, `engine`, `datetime`, `searcher`)
        VALUES (
        '".mysqli_real_escape_string($link, $_POST['searchTerm'])."',
        '".mysqli_real_escape_string($link, $_POST['engine'])."',
        '".date('Y-m-d H:i:s')."',
        '1');";

    if (mysqli_query($link, $query)) {
        echo 'data inserted';
    } else {
        echo 'failed to enter into database. Query was: <b>'.$query.'</b>';
    }
    die();
}

// Search term in database
if (array_key_exists('searchTerm', $_POST)) {
    if ($_POST['searchTerm']!='') {
        $query = "SELECT * FROM `searches` WHERE `searchTerm` LIKE '%".mysqli_real_escape_string($link, $_POST['searchTerm'])."%' LIMIT 10";

        $result = mysqli_query($link, $query);
        if (mysqli_num_rows($result) > 0) {
            echo '<ul>';
            while ($row = mysqli_fetch_array($result)) {
                $onclickattribute = "document.getElementById('searchField').value ='".$row['searchTerm']."'";
                echo '<li><a href="#" onclick="'.$onclickattribute.'">'.$row['searchTerm'].'</a> <small><code>('.$row['engine'].')</code> (Date: '.$row['datetime'].')</small></li>';
            }
            echo '</ul>';
        }
    }
    die();
}

// add new engine
if (array_key_exists('sitename', $_POST) and $_POST['sitename']!='' and
array_key_exists('identifier', $_POST) and $_POST['identifier']!='' and
array_key_exists('urlprefix', $_POST) and $_POST['urlprefix']!='') {
    $query_CheckUnique = "SELECT * FROM `engines` WHERE identifier='".mysqli_real_escape_string($link, $_POST['identifier'])."' LIMIT 1";
    $result_CheckUnique = mysqli_query($link, $query_CheckUnique);

    if ($result_CheckUnique == false) {
        echo 'That engine already exists';
    } else {
        $query_insertEngine = "INSERT INTO `engines` (`site name`, `identifier`, `url-prefix`, `url-suffix`)
        VALUES (
        '".mysqli_real_escape_string($link, $_POST['sitename'])."',
        '".mysqli_real_escape_string($link, $_POST['identifier'])."',
        '".mysqli_real_escape_string($link, $_POST['urlprefix'])."',
        '".mysqli_real_escape_string($link, $_POST['urlsuffix'])."');";

        if (mysqli_query($link, $query_insertEngine)) {
            echo 'engine inserted';
        } else {
            echo 'failed to enter into database. Query was: <b>'.$query_insertEngine.'</b>';
        }
    }
    die();
}
?>

<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.min.css" rel="stylesheet" />


    <style type="text/css">
    #results {
        display: none;
    }

    .select2-container .select2-selection--single {
        height: 34px !important;
        margin-bottom: 8px;
    }
    </style>

    <title>Search Stuff</title>
</head>

<body>
    <div class="container">
        <h1>Smart Search</h1>

        <form class="form-inline" method="get" id="searchForm">

            <select name="engine" class="form-control" id="engine">
                <?php
                    $query_engines = "SELECT * FROM `engines`";
                    $result_engines = mysqli_query($link, $query_engines);
                    while ($row_engines = mysqli_fetch_array($result_engines)) {
                        $identifier=$row_engines['identifier'];
                        $sitename = $row_engines['site name'];
                        echo "<option value='".$identifier."'>".$sitename." (".$identifier.")</option>";
                    }
                ?>
            </select>

            <div class="form-group mx-sm-3 mx-2">
                <input type="text" name="searchTerm" class="form-control" id="searchField" autocomplete="off"
                    placeholder="Enter search term here" <?php
                if (array_key_exists('searchTerm', $_GET)) {
                    echo 'value="'.$_GET['searchTerm'].'"';
                }
                ?>>
            </div>

            <button type="submit" class="btn btn-primary mx-2">Submit</button>

        </form>


        <div id="results">
            <!-- Search results are displayed by ajax here-->
        </div>

        <div id="newengine">
            <h3>New Engine Maker</h3>
            <div id="errorInNewEngineMaker"></div>

            <form class="form-inline" id="newEngineMake">
                <div class="form-group mx-sm-3 mb-2">
                    <input type="text" name="sitename" id="sitename" class="form-control" placeholder="Site Name">
                </div>

                <div class="form-group mx-sm-3 mb-2">
                    <input type="text" name="identifier" id="identifier" class="form-control"
                        placeholder="Unique Identifier">
                </div>

                <div class="form-group mx-sm-3 mb-2">
                    <input type="text" name="urlprefix" id="urlprefix" class="form-control" placeholder="URL Prefix">
                </div>

                <div class="form-group mx-sm-3 mb-2">
                    <input type="text" name="urlsuffix" id="urlsuffix" class="form-control" placeholder="URL Suffix">
                </div>

                <button type="submit" class="btn btn-primary mb-2">Submit</button>
            </form>

        </div>

        <h3>History</h3>
        <ol>
            <?php
                $query = "SELECT * FROM `searches` ORDER BY `id` DESC LIMIT 100";
                $result = mysqli_query($link, $query);
                while ($row = mysqli_fetch_array($result)) {
                    $onclickattribute = "document.getElementById('searchField').value ='".$row['searchTerm']."'";
                    
                    echo '<li><label onclick="'.$onclickattribute.'"><a href="#">'.$row['searchTerm'].'</a><small> <code>('.$row['engine'].')</code> (Date: '.$row['datetime'].')</small></label></li>';
                }
            ?>
        </ol>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js"></script>

    <script type="text/javascript">
    //making script select2
    $(document).ready(function() {
        $('#engine').select2();
    });

    //Searches with instant database results
    $("#searchField").keyup(function() {
        var searchTerm = $("#searchField").val();
        $.ajax({
            type: "POST",
            url: "searchdb.php",
            data: {
                searchTerm: searchTerm
            },
            success: function(result) {
                if (result != '') {
                    console.log(result);
                    $("#results").show();
                    $("#results").html("<b>Results</b>:<br/>" + result);
                } else {
                    $("#results").hide();
                }
            }
        })
    })

    $("#newEngineMake").submit(function(e) {
        e.preventDefault(); // avoid to execute the actual submit of the form.

        var sitename = $("#sitename").val();
        var identifier = $("#identifier").val();
        var urlprefix = $("#urlprefix").val();
        var urlsuffix = $("#urlsuffix").val();

        if (sitename != '' && identifier != '' && urlprefix != '') {
            $.ajax({
                type: "POST",
                url: "searchdb.php",
                data: {
                    sitename: sitename,
                    identifier: identifier,
                    urlprefix: urlprefix,
                    urlsuffix: urlsuffix
                },
                success: function(data) {
                    alert(data);
                }
            });
        } else {
            alert('Please fill at least first 3 boxes');
            $("#sitename").focus();
        }

    });

    $("#searchForm").submit(function(e) {
        e.preventDefault(); // avoid to execute the actual submit of the form.

        var searchTerm = $("#searchField").val();
        var engine = $("#engine").val();

        if (searchTerm != '') {
            $.ajax({
                type: "POST",
                url: "searchdb.php",
                data: {
                    searchTerm: searchTerm,
                    engine: engine,
                    submit: 'submit'
                },
                success: function(data) {
                    openup(engine, searchTerm);
                }
            });
        } else {
            alert('Please Enter a Term');
            $("#searchTerm").focus();
        }

    });
    </script>
    <?php
        echo '<script>';
        echo 'function openup(engine, search) {
            ';

        mysqli_data_seek($result_engines, 0);
        while ($row_engines = mysqli_fetch_array($result_engines)) {
            $identifier=$row_engines['identifier'];
            $prefix = $row_engines['url-prefix'];
            $suffix = $row_engines['url-suffix'];

            echo 'if (engine == "'.$identifier.'"){
                open("'.$prefix.'"+search+"'.$suffix.'")
            }
            ';
        }
        
        echo '}';
        echo '</script>';
    ?>
</body>

</html>