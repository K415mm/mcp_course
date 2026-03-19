<?php
function testKroki($xml) {
    $c = gzcompress($xml, 9); 
    $b = base64_encode($c); 
    $u = str_replace(['+', '/', '='], ['-', '_', ''], $b);
    $url = 'https://kroki.io/diagramsnet/svg/' . $u;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Status: $status\nResult: $res\n\n";
}

$xml1 = '<mxGraphModel><root><mxCell id="0"/><mxCell id="1" parent="0"/></root></mxGraphModel>';
echo "Test 1 (mxGraphModel):\n";
testKroki($xml1);

$xml2 = '<mxfile><diagram id="xyz" name="Page-1"><mxGraphModel><root><mxCell id="0"/><mxCell id="1" parent="0"/></root></mxGraphModel></diagram></mxfile>';
echo "Test 2 (mxfile):\n";
testKroki($xml2);
