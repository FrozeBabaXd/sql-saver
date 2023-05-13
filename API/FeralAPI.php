<?php
// Sad & invisible : @endercoder
// Kodları degistirmek serbesttir sorumluluk kullanıcıya aittir.
// Eğitim ve deneyim için yapılmıstır.

$API = ""; // urlnin sonunda TC inputu zorunlu

require '../../../include/antiXss.php';
header("Content-Type: application/json; charset=utf-8");
ini_set('display_errors', 0);



$Command = $xss->xss_clean($_POST["Cmd"]);

if ($Command == "Start") {

    function giver()
    {
		// 101M data PDO baglantısı
        $servername = "localhost";
        $database = "<db>";
        $username = "<usr>";
        $password = "<pw>";

        $db = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $X = $db->prepare("SELECT TC FROM 101m LIMIT 1");
        $X->execute();
        $row = $X->fetch(PDO::FETCH_ASSOC);
        $TC = $row["TC"];

        $X = $db->prepare("DELETE FROM 101m WHERE TC=?");
        $X->execute([$TC]);
        return $TC;
    }

    function saver($TC)
    {
		// Verinin kaydedilecegi db PDO baglantısı
        $servername = "localhost";
        $database = "<db>";
        $username = "<usr>";
        $password = "<pw>";

        $db = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "$API$TC",
            CURLOPT_RETURNTRANSFER => true
        ]);
        $res = curl_exec($ch);
        curl_close($ch);

        $jd = json_decode($res);
		
		// API'nin json çıktısı (Client bağlantısı)
        $result = $jd->data->Adres;
		$mess = $jd->Message;
        
		$data = $result;
		// ALTTA OLAN SATIR VERI BULUNAMADIGINDA YAPACAGI CIKTI ILE ILGILIDIR (ZORUNLU)
        if ($mess === "Bulunamadi") {
            $var = ["status" => "400", "message" => "Adres bulunamadı: -> $TC"];
		// ALTTA OLAN SATIR VERI VARSA YAPACAGI ISLEM ILE ILGILIDIR
        } elseif ($data != null) {
            $X = $db->prepare("INSERT IGNORE INTO `adres` SET `TC`=?, `ADDRESS`=?");
            $X->execute([$TC, $data]);
            if ($X) {
                $var = ["status" => "200", "message" => "Başarılı: $TC"];
            } else {
                $var = ["status" => "400", "message" => "Hata: $TC"];
            }
        }
        return $var;
    }

    $Event = saver(giver());

    echo json_encode($Event, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["status" => "false", "message" => "Usage: ?Cmd=Start"], JSON_PRETTY_PRINT);
}
