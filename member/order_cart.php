<?php
session_start();
$id = $_POST['id'];
$action = $_POST['action'];

if (!isset($_SESSION["strProductID"])) {
    $_SESSION["intLine"] = 0;
    $_SESSION["strProductID"][0] = $id;
    $_SESSION["strQty"][0] = 1;
} else {
    $key = array_search($id, $_SESSION["strProductID"]);
    if ((string)$key !== "") {
        if ($action === 'increase') {
            $_SESSION["strQty"][$key]++;
        } elseif ($action === 'decrease' && $_SESSION["strQty"][$key] > 1) {
            $_SESSION["strQty"][$key]--;
        }
    } else {
        $_SESSION["intLine"]++;
        $intNewLine = $_SESSION["intLine"];
        $_SESSION["strProductID"][$intNewLine] = $id;
        $_SESSION["strQty"][$intNewLine] = 1;
    }
}

echo "updated";