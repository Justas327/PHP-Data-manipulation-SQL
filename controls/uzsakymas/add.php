<?php
include 'services/uzsakymas.php';
include 'services/zaislas.php';
include 'services/zaisloUzsakymas.php';

$uzsakService = new uzsakymas();
$zaisluService = new zaislas();
$zaisloUzsakService = new zaisloUzsakymas();

if (!empty($_POST['submit'])) {
//    var_dump($_POST);
    include "includes/validator.php";
    $validator = new validator();
    $uzsakPardErr = $validator->validate($_POST['fk_PARDUOTUVEnr'], "int");
    $fabrikErr = $validator->validate($_POST['fb_FABRIKASid_FABRIKAS'], "int");
    $uzsakBusErr = $validator->validate($_POST['busena'], "int");

    if ($uzsakPardErr && $uzsakBusErr && $fabrikErr) {

        $arVisiGeri = true;
        foreach ($_POST['fk_ZAISLASid'] as $key => $value) {
            if (empty($value) && empty($_POST['Kiekis'][$key]))
                continue;

            $kiekisErr = $validator->validate($_POST['Kiekis'][$key], "float");
            $zaisloIdErr = $validator->validate($_POST['fk_ZAISLASid'][$key], "int");

            if (!($kiekisErr && $zaisloIdErr)) {
                $arVisiGeri = false;
                break;
            }
        }

        if (!$arVisiGeri) {
            $data = $_POST;
            $_GET['error'] = 2;
        } else {
            $currentDate = date('Y-m-d');

            $uzsakymas['uzsakymo_data'] = $currentDate;
            $uzsakymas['busena'] = $_POST['busena'];
            $uzsakymas['fk_FABRIKASid_FABRIKAS'] = $_POST['fb_FABRIKASid_FABRIKAS'];
            $uzsakymas['fk_PARDUOTUVEnr'] = $_POST['fk_PARDUOTUVEnr'];

            $result = $uzsakService->insertOrder($uzsakymas);

            if (!$result) {
                $_GET['error'] = 4;
                $data = $_POST;
            } else {
                foreach ($_POST['fk_ZAISLASid'] as $key => $value) {
                    if (empty($value) && empty($_POST['Kiekis'][$key]))
                        continue;

                    $zaisloUzsakymas['Kiekis'] = $_POST['Kiekis'][$key];
                    $zaisloUzsakymas['fk_UZSAKYMASnr'] = $uzsakService->getNextID() - 1;
                    $zaisloUzsakymas['fk_ZAISLASid'] = $value;

                    $result = $zaisloUzsakService->insertOrder($zaisloUzsakymas);
                    if (!$result) {
                        $_GET['error'] = 3;
                    }
                }
            }
        }

        if (!isset($_GET['error']))
            header("Location: index.php?module={$module}&action=list");
        else
            $data = $_POST;

    } else {
        $data = $_POST;
        $_GET['error'] = 1;
    }
}

include 'templates/uzsakymas/form.php';
