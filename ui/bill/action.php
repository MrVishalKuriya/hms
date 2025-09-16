<?php
$GLOBALS['title']="Bill-HMS";
$base_url="http://localhost/hms/";

require('./../../inc/sessionManager.php');
require('./../../inc/dbPlayer.php');

$ses = new \sessionManager\sessionManager();
$ses->start();

$db = new \dbPlayer\dbPlayer();
$msg = $db->open();
if($msg !== "true"){
    echo '<script>alert("DB Connection Failed: '.$msg.'"); window.location.href="view.php";</script>';
    exit;
}

// Save/Update bill
if(isset($_POST["btnSave"])){
    $billId = intval($ses->Get("billIdFor"));
    $userId = intval($ses->Get("userId"));
    $rows = count($_POST['type']);
    $count = 0;

    // Delete previous records
    $db->delete("DELETE FROM billing WHERE billId='$billId'");

    for($i=0; $i<$rows; $i++){
        if($_POST['type'][$i] !== "" && $_POST['amount'][$i] !== ""){
            $data = [
                'billId'=>$billId,
                'type'=>$_POST['type'][$i],
                'amount'=>floatval($_POST['amount'][$i]),
                'userId'=>$userId,
                'billingDate'=>date("Y-m-d")
            ];
            $res = $db->insertData("billing",$data);
            if($res >= 0) $count++;
        }
    }

    if($count == $rows){
        echo '<script>alert("Bill ['.$billId.'] updated successfully."); window.location.href="view.php";</script>';
    } else {
        echo '<script>alert("Error updating bill."); window.location.href="view.php";</script>';
    }
    exit;
}

// Delete bill
if(isset($_GET['id']) && isset($_GET['wtd']) && $_GET['wtd'] === "delete"){
    $billId = intval($_GET['id']);
    $res = $db->delete("DELETE FROM billing WHERE billId='$billId'");
    if($res >= 0){
        echo '<script>alert("Bill Deleted Successfully."); window.location.href="view.php";</script>';
    } else {
        echo '<script>alert("Error deleting bill."); window.location.href="view.php";</script>';
    }
    exit;
}

// Render update form
function formRender($data,$billId){ ?>
<?php include('./../../master.php'); ?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header titlehms"><i class="fa fa-hand-o-right"></i>Bill Update</h1>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading text-success">
            <i class="fa fa-info-circle fa-fw"></i>Bill Update <label
                class="text-success">[<?php echo $billId ?>]</label>
        </div>
        <div class="panel-body">
            <form action="action.php" method="post">
                <div class="table-responsive">
                    <table id="billingTable" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Bill Type</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($data)){ ?>
                            <tr>
                                <td><input type="checkbox" name="chkbox[]" /></td>
                                <td><input type="text" name="type[]" class="form-control"
                                        value="<?php echo htmlspecialchars($row['type']); ?>" /></td>
                                <td><input type="text" name="amount[]" class="form-control"
                                        value="<?php echo htmlspecialchars($row['amount']); ?>" /></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="btnSave" class="btn btn-success"><i class="fa fa-check"></i> Save</button>
            </form>
        </div>
    </div>
</div>
<?php include('./../../footer.php'); ?>

<script>
function addRow(tableID) {
    var table = document.getElementById(tableID);
    var row = table.insertRow(table.rows.length);
    row.insertCell(0).innerHTML = '<input type="checkbox" name="chkbox[]"/>';
    row.insertCell(1).innerHTML = '<input type="text" name="type[]" class="form-control"/>';
    row.insertCell(2).innerHTML = '<input type="text" name="amount[]" class="form-control"/>';
}

function deleteRow(tableID) {
    var table = document.getElementById(tableID);
    for (var i = 0; i < table.rows.length; i++) {
        var chk = table.rows[i].cells[0].childNodes[0];
        if (chk && chk.checked) {
            table.deleteRow(i);
            i--;
        }
    }
}
</script>
<?php } ?>