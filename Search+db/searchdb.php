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
                $onclickattribute = "document.getElementById('searchField').value ='".$row['searchTerm']."'; $('#searchField').keyup()";
                echo '<li><label class="historyList" onclick="'.$onclickattribute.'"><span class="text-primary">'.$row['searchTerm'].'</span> <small><code>('.$row['engine'].')</code> (Date: '. date("d-M-Y h:i:s a", strtotime($row['datetime'])).')</small></label></li>';
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
        $query_insertEngine = "INSERT INTO `engines` (`site name`, `identifier`, `url-prefix`, `url-suffix`, `nsfw`)
        VALUES (
        '".mysqli_real_escape_string($link, $_POST['sitename'])."',
        '".mysqli_real_escape_string($link, $_POST['identifier'])."',
        '".mysqli_real_escape_string($link, $_POST['urlprefix'])."',
        '".mysqli_real_escape_string($link, $_POST['urlsuffix'])."',
        '".mysqli_real_escape_string($link, $_POST['nsfw'])."');";

        if (mysqli_query($link, $query_insertEngine)) {
            echo 'engine inserted';
        } else {
            echo 'failed to enter into database. Query was: <b>'.$query_insertEngine.'</b>';
        }
    }
    die();
}

// delete a history entry
if (array_key_exists('delete_history', $_POST) and array_key_exists('id', $_POST) and $_POST['id']!='') {
    $query_delete_history = "DELETE FROM `searches` WHERE `searches`.`id` = ".mysqli_real_escape_string($link, $_POST['id'])." LIMIT 1";
    if (mysqli_query($link, $query_delete_history)) {
        echo 'history deleted';
    } else {
        echo 'failed to delete. Query was: <b>'.$query_delete_history.'</b>';
    }
    die();
}

// change Engine
if (array_key_exists('changeEngine', $_POST)) {
    if ($_POST['changeEngine'] == 'changeEngine') {
        // find all details about the engine as requested
        $query = "SELECT * FROM `engines` WHERE identifier='".mysqli_real_escape_string($link, $_POST['engine'])."' LIMIT 1";
    
        $result = mysqli_query($link, $query);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            echo json_encode($row);
        } else {
            echo 'failed to find. Query was: <b>'.$query.'</b>';
        }
    }
    if ($_POST['changeEngine'] == 'update') {
        // update engine info
        $query = "UPDATE `engines` 
        SET `site name` = '".mysqli_real_escape_string($link, $_POST['sitename'])."',
        `url-prefix` = '".mysqli_real_escape_string($link, $_POST['urlprefix'])."',
        `url-suffix` = '".mysqli_real_escape_string($link, $_POST['urlsuffix'])."',
        `nsfw` = ".mysqli_real_escape_string($link, $_POST['nsfw'])."
        WHERE id = ".mysqli_real_escape_string($link, $_POST['id'])." LIMIT 1";

        if (mysqli_query($link, $query)) {
            echo 'success';
        } else {
            echo 'failed to update. Query was: <b>'.$query.'</b>';
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

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/9c2d6b042e.js"></script>

    <style type="text/css">
    #results {
        display: none;
    }

    .select2-container .select2-selection--single {
        height: 34px !important;
        margin-bottom: 0px;
    }

    .historyList {
        cursor: pointer;
    }

    .historyList:hover {
        background-color: pink;
    }

    .delete_history {
        transition: font-size 0.2s;
        margin-left: 10px;
    }

    .delete_history:hover {
        font-size: 1.2em;
        color: red;
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
            <span class="btn btn-primary mx-2" id="edit-engine"><i class="fas fa-edit"></i> Edit Engine</span>
        </form>


        <div id="results">
            <!-- Search results are displayed by ajax here-->
        </div>

        <div id="newengine">
            <h3>New Engine Maker</h3>
            <div id="errorInNewEngineMaker"></div>

            <form class="form-inline" id="newEngineMake">
                <div class="form-group mr-sm-3 mb-2">
                    <input type="text" name="sitename" id="sitename" class="form-control" placeholder="Site Name">
                </div>

                <div class="form-group mr-sm-3 mb-2">
                    <input type="text" name="identifier" id="identifier" class="form-control"
                        placeholder="Unique Identifier">
                </div>

                <div class="form-group mr-sm-3 mb-2">
                    <input type="text" name="urlprefix" id="urlprefix" class="form-control" placeholder="URL Prefix">
                </div>

                <div class="form-group mr-sm-3 mb-2">
                    <input type="text" name="urlsuffix" id="urlsuffix" class="form-control" placeholder="URL Suffix">
                </div>

                <div class="form-group mr-sm-3 mb-2">
                    <select name="nsfw" id="nsfw" class="form-control">
                        <option value="0" hidden>NSFW?</option>
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary mb-2">Submit</button>
            </form>

        </div>
        <pre>
        to-do:
        - Change the engine as well when clicking on link
        </pre>
        <h3>History</h3>
        <p class="small">
            <a href="?filter=sfw-only">SFW</a> |
            <a href="?filter=nsfw-only">NSFW</a> |
            <a href="?filter=all">All</a>
        </p>
        <ol>
            <?php
                if (array_key_exists('filter', $_GET)) {
                    if ($_GET['filter'] == 'sfw-only') {
                        $query = "SELECT * FROM `searches` INNER JOIN `engines` ON searches.engine = engines.identifier AND engines.nsfw='0' ORDER BY searches.id DESC LIMIT 100";
                    }
                    if ($_GET['filter'] == 'nsfw-only') {
                        $query = "SELECT * FROM `searches` INNER JOIN `engines` ON searches.engine = engines.identifier AND engines.nsfw='1' ORDER BY searches.id DESC LIMIT 100";
                    }
                    if ($_GET['filter'] == 'all') {
                        $query = "SELECT * FROM `searches` ORDER BY searches.id DESC LIMIT 100";
                    }
                } else {
                    $query = "SELECT * FROM `searches` INNER JOIN `engines` ON searches.engine = engines.identifier AND engines.nsfw='0' ORDER BY searches.id DESC LIMIT 100";
                }
                $result = mysqli_query($link, $query);
                while ($row = mysqli_fetch_array($result)) {
                    $onclickattribute = "document.getElementById('searchField').value ='".$row['searchTerm']."'; document.getElementById('searchField').focus(); $('#searchField').keyup()";
                    
                    echo '<li><label class="historyList" onclick="'.$onclickattribute.'"><span class="text-primary">'.$row['searchTerm'].'</span><small> <code>('.$row['engine'].')</code> (Date: '. date("d-M-Y h:i:s a", strtotime($row['datetime'])).')</small></label> <a href="#" class="delete_history" data-id="'.$row['id'].'"><i class="far fa-trash-alt"></i></a></li>';
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
        if (document.title != searchTerm && searchTerm != '') {
            var engine = $("#engine").val();
            document.title = 'SearchStuff: (' + engine + ')  ' + searchTerm;
        } else {
            document.title = 'SearchStuff!';
        }

        $.ajax({
            type: "POST",
            url: "searchdb.php",
            data: {
                searchTerm: searchTerm
            },
            success: function(result) {
                if (result != '') {

                    $("#results").show();
                    $("#results").html("<b>Results</b>:<br/>" + result);
                } else {
                    $("#results").hide();
                }
            }
        })
    })

    // make new search engine
    $("#newEngineMake").submit(function(e) {
        e.preventDefault();

        var sitename = $("#sitename").val();
        var identifier = $("#identifier").val();
        var urlprefix = $("#urlprefix").val();
        var urlsuffix = $("#urlsuffix").val();
        var nsfw = $("#nsfw").val();

        if (sitename != '' && identifier != '' && urlprefix != '') {
            $.ajax({
                type: "POST",
                url: "searchdb.php",
                data: {
                    sitename: sitename,
                    identifier: identifier,
                    urlprefix: urlprefix,
                    urlsuffix: urlsuffix,
                    nsfw: nsfw
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

    // save search to db
    $("#searchForm").submit(function(e) {
        e.preventDefault();

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

    // delete history
    $(".delete_history").click(function(e) {
        e.preventDefault();

        if (confirm("Sure you want to delete this entry?")) {
            $.ajax({
                type: "POST",
                url: "searchdb.php",
                data: {
                    delete_history: 'yes',
                    id: this.dataset.id
                },
                success: function(data) {
                    alert(data);
                }
            });
        }
    });

    // edit engine
    $("#edit-engine").click(function(e) {
        //e.preventDefault();

        var engine = $("#engine").val();

        $.ajax({
            type: "POST",
            url: "searchdb.php",
            data: {
                changeEngine: 'changeEngine',
                engine: engine
            },
            success: function(data) {
                var modal = $('#changeSearchEngineDetails')
                modal.modal('show')

                data = JSON.parse(data)

                modal.find('#modal-id').val(data.id)
                modal.find('#modal-identifier').val(data.identifier)
                modal.find('.modal-body #sitename').val(data["site name"])
                modal.find('.modal-body #url-prefix').val(data["url-prefix"])
                modal.find('.modal-body #url-suffix').val(data["url-suffix"])
                modal.find('.modal-body #nsfw').val(data.nsfw)
                modal.find('#updateEngineButton').val('Update ' + data.identifier)
            }
        });
    })

    // actually update the engine
    $(document).on('click', '#updateEngineButton', function(e) {
        e.preventDefault();

        var modal = $('#changeSearchEngineDetails')

        $.ajax({
            type: "POST",
            url: "searchdb.php",
            data: {
                changeEngine: 'update',
                id: modal.find('#modal-id').val(),
                sitename: modal.find('.modal-body #sitename').val(),
                urlprefix: modal.find('.modal-body #url-prefix').val(),
                urlsuffix: modal.find('.modal-body #url-suffix').val(),
                nsfw: modal.find('.modal-body #nsfw').val()
            },
            success: function(data) {
                console.log(data)
                if (data == 'success') {
                    modal.modal('hide')
                } else {
                    alert(data)
                }
            }
        });
    })
    </script>

    <div class="modal fade" id="changeSearchEngineDetails" tabindex="-1" role="dialog"
        aria-labelledby="changeSearchEngine_Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeSearchEngine_Label">Edit Engine</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="modal-id" class="col-form-label">ID</label>
                            <input type="text" class="form-control" id="modal-id" disabled>
                        </div>
                        <div class="form-group">
                            <label for="modal-identifier" class="col-form-label">Identifier</label>
                            <input type="text" class="form-control" id="modal-identifier" disabled>
                        </div>
                        <div class="form-group">
                            <label for="sitename" class="col-form-label">Site Name:</label>
                            <input type="text" class="form-control" id="sitename">
                        </div>
                        <div class="form-group">
                            <label for="url-prefix" class="col-form-label">URL Prefix:</label>
                            <input type="text" class="form-control" id="url-prefix">
                        </div>
                        <div class="form-group">
                            <label for="url-suffix" class="col-form-label">URL Suffix:</label>
                            <input type="text" class="form-control" id="url-suffix">
                        </div>
                        <div class="form-group">
                            <label for="nsfw" class="col-form-label">NSFW</label>
                            <input type="text" class="form-control" id="nsfw">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="updateEngineButton">Update Engine</button>
                </div>
            </div>
        </div>
    </div> <!-- End modal -->
    <?php
        // generate javascript for every search engine
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