<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa cidades</title>
    <link rel="stylesheet" href="style/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css"/>
</head>
<body>
        <header>
            <div>
                <form action="" method="post" class="search-form">
                    <input type="text" name="name" id="name" placeholder="Cidade" class="search-input">
                    <button class="botao">Pesquisar</button>
                </form>
            </div>
        </header>
    <?php

    include "apiKey.php";

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['name'])) {
        
        $name = str_replace(" ","_",htmlspecialchars($_POST['name']));

        $curl = curl_init();
        curl_setopt_array($curl, [

        CURLOPT_URL => "http://api.openweathermap.org/geo/1.0/direct?q=". $name . "&limit=1&appid=" . $apiKey,

        CURLOPT_RETURNTRANSFER => true
        ]);
        $geoResponse = curl_exec($curl);
        curl_close($curl);

        $cities = json_decode($geoResponse, true);
        
        
        if(!empty($cities)){
            foreach ($cities as $city){
                $lat = $city['lat'];
                $lon = $city['lon'];
                $cityName = $city['name'];

                $curl = curl_init();
                    curl_setopt_array($curl, [

                    CURLOPT_URL => "http://api.openweathermap.org/data/2.5/air_pollution?lat=".$lat."&lon=".$lon."&appid=" . $apiKey,

                    CURLOPT_RETURNTRANSFER => true
                ]);
                
                $airResponse = curl_exec($curl);
                curl_close($curl);

                $air = json_decode($airResponse, true);

                if (!empty($air['list'])) {
                    $airQuality = $air['list'][0]['main']['aqi'];
                    $gas = $air['list'][0]['components'];

                    switch($airQuality) {
                        case "1":
                            $quality = "Ótimo";
                            $qualityClass = "quality-good";
                            $qualityBackgroundColor = "background-color: #008000;";
                            $recommendation = "Bom para atividades ao ar livre";
                            break;
                        case "2":
                            $quality = "Boa";
                            $qualityClass = "quality-fair";
                            $qualityBackgroundColor = "background-color: #759e25;";
                            $recommendation = "Atividades ao ar livre são aceitáveis";
                            break;
                        case "3":
                            $quality = "Moderada";
                            $qualityClass = "quality-moderate";
                            $qualityBackgroundColor = "background-color: #8a8a0a;";
                            $recommendation = "Evite atividades intensas ao ar livre";
                            break;
                        case "4":
                            $quality = "Ruim";
                            $qualityClass = "quality-poor";
                            $qualityBackgroundColor = "background-color: #CD3333;";
                            $recommendation = "Evite atividades físicas ao ar livre";
                            break;
                        case "5":
                            $quality = "Péssima";
                            $qualityClass = "quality-very-poor";
                            $qualityBackgroundColor = "background-color: #ff0000;";
                            $recommendation = "Evite sair de casa";
                            break;
                    }

              
                }
                else {
                    $airError = "Informações sobre a qualidade do ar não foram encontradas";
                }
            }
        }
        else{
            $geoError = "Nenhuma cidade encontrada";
        }

    }
    ?>
            
    <?php if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['name']) && !empty($cities)) :?>
        <main>

            <div id="lat" style="display: none;"><?=$lat?></div>
            <div id="lon" style="display: none;"><?=$lon?></div>
            <div>

                <div class="city-box">
                    <div class="city-name">
                        <div>Cidade</div>
                        <h1><?=$cityName?></h1>
                    </div>
                    <div class="quality-and-info">    
                        <div class="quality-box">
                            <div class="left-panel">
                                <h2>Índice de qualidade do ar: <span class="<?=$qualityClass?>"><?=$quality?></span></h2>
                                <h3>Componentes de poluição - (em μg/m³)</h3>
                                <div class="responsive">
                                    <table class="pollution-table">
                                        <thead style="<?= $qualityBackgroundColor?>">
                                            <tr>
                                                <th>PM2.5</th>
                                                <th>PM10</th>
                                                <th>NO₂</th>
                                                <th>SO₂</th>
                                                <th>O₃</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?=$gas['pm2_5']?></td>
                                                <td><?=$gas['pm10']?></td>
                                                <td><?=$gas['so2']?></td>
                                                <td><?=$gas['no2']?></td>
                                                <td><?=$gas['o3']?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="recommendation">
                                    <h3>Recomendações: <span class="<?=$qualityClass?>"><?= $recommendation ?></span>;</h3>
                                </div>
                            </div>
                        </div>
                        <div class="subtitles"> <!-- subtitles -->
                            <div class="subtitles-box">
                                <h2 style="margin-left: 10px;">Legenda:</h2>
                                <div class="cores-indicacoes">
                                    <div class="sq-good"></div><span style="margin-left: 4px;">Ótimo</span>
                                </div>
                                <div class="cores-indicacoes">
                                    <div class="sq-fair"></div><span style="margin-left: 4px;">Boa</span>
                                </div>
                                <div class="cores-indicacoes">
                                    <div class="sq-moderate"></div><span style="margin-left: 4px;">Moderada</span>
                                </div>
                                <div class="cores-indicacoes">
                                    <div class="sq-poor"></div><span style="margin-left: 4px;">Ruim</span>
                                </div>
                                <div class="cores-indicacoes">
                                    <div class="sq-very-poor"></div><span style="margin-left: 4px;">Péssima</span>
                                </div>
                            </div>
                        </div>
                    </div>
                        <div class="right-panel">
                            <div id="map" style="height: 400px;margin-top:20px;position:relative"></div>
                        </div>
                    
                </div>
            </main>
        <?php elseif(!empty($cities) && empty($air)) :?>
            <main>
                <div class="error-message"><?= $airError ?></div>
            </main>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['name']) && empty($cities)) :?>
            <main>
                <div class="error-message"><?= $geoError ?></div>
            </main>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST" && empty($_POST['name'])) :?>
            <main>
                <div class="error-message"> Informe um nome de uma cidade para fazer a pesquisa! </div>
            </main>
        <?php endif ;?>
        </div>
    </body>
    <script src="javascript/script.js"></script> 
</html>