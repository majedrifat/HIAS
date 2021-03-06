<?php

    class COVID19
    {

        function __construct($_GeniSys)
        {
            $this->_GeniSys = $_GeniSys;

            $this->country = "Spain";
            $this->period = "Year";
            $this->stat = "Deaths";

            $this->dataURL = 'https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_daily_reports/';
        }

        public function getCOVID19($params = [])
        {
            if($params["period"]=="Day"):
                $dater = "WHERE date > DATE_SUB(DATE(CURDATE()), INTERVAL 1 DAY) ";
            elseif($params["period"]=="Week"):
                $dater = "WHERE date >= DATE(NOW()) - INTERVAL 7 DAY ";
            elseif($params["period"]=="Month"):
                $dater = "WHERE date > DATE_SUB(DATE(CURDATE()), INTERVAL 1 MONTH)  ";
            elseif($params["period"]=="Year"):
                $dater = "WHERE date > DATE_SUB(DATE(CURDATE()), INTERVAL 1 YEAR)  ";
            endif;

            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT date,
                    sum(confirmed) as confirmed,
                    sum(deaths) as deaths,
                    sum(active) as active,
                    sum(recovered) as recovered
                FROM covid19data  
                $dater
                && Country = :country
                GROUP BY Country, date
                ORDER BY date ASC
            ");
            $pdoQuery->execute([
                ":country" => $params["country"]
            ]);
            $response=$pdoQuery->fetchAll(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;
            return $response;
        }

        public function getCOVID19Periods()
        {
            $year = $this->getCOVID19([
                "period" => "Year",
                "country" => $this->country
            ]);

            $month = $this->getCOVID19([
                "period" => "Month",
                "country" => $this->country
            ]);

            $week = $this->getCOVID19([
                "period" => "Week",
                "country" => $this->country
            ]);

            $yeard = array_column($year, 'deaths');
            $yearddate = array_column($year, 'dates');

            $monthd = array_column($month, 'deaths');
            $monthddate = array_column($month, 'dates');

            $weekd = array_column($week, 'deaths');
            $weekddate = array_column($week, 'dates');

            $yeara = array_column($year, 'active');
            $montha = array_column($month, 'active');
            $weeka = array_column($week, 'active');

            return [$yeard, $monthd, $weekd, $yearddate, $monthddate, $weekddate];
            
        }

        public function getCOVID19MonthDeaths($month)
        {

            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT sum(deaths) as deaths
                FROM covid19data  
                WHERE MONTH(Date) = :date 
                && Country = :country
                GROUP BY Country, date
                ORDER BY date ASC
            ");
            $pdoQuery->execute([
                ":date" => $month,
                ":country" => $this->country
            ]);
            $response=$pdoQuery->fetchAll(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;

            return array_column($response, 'deaths');
            
        }

        public function getCOVID19MonthActive($month)
        {

            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT sum(confirmed) as confirmed
                FROM covid19data  
                WHERE MONTH(Date) = :date 
                && Country = :country
                GROUP BY Country, date
                ORDER BY date ASC
            ");
            $pdoQuery->execute([
                ":date" => $month,
                ":country" => $this->country
            ]);
            $response=$pdoQuery->fetchAll(PDO::FETCH_ASSOC);
            $pdoQuery->closeCursor();
            $pdoQuery = null;

            return array_column($response, 'confirmed');
            
        }

        public function getCOVID19Totals($params = [])
        {
            $covid19d = $this->getCOVID19([
                "period" => $this->period,
                "country" => $this->country
            ]);

            $active = array_column($covid19d, 'active');
            $recovered = array_column($covid19d, 'recovered');
            $deaths = array_column($covid19d, 'deaths');
            $dates = array_column($covid19d, 'date');

            $periods = $this->getCOVID19Periods();
            
            if($this->stat == "Deaths"):
                $cstats = $deaths;
            elseif($this->stat == "Active"):
                $cstats = $active;
            elseif($this->stat == "Recovered"):
                $cstats = $recovered;
            endif;

            return [$cstats, $active[count($active)-1], $recovered[count($recovered)-1], 
                    $deaths[count($deaths)-1], $dates, $periods[0], $periods[1], $periods[2]];
        }

        public function updateCOVID()
        {

            $pdoQuery = $this->_GeniSys->_secCon->prepare("
                SELECT pulldate
                FROM covid19pulls 
                ORDER BY id DESC
            ");
            $pdoQuery->execute();
            $response=$pdoQuery->fetch(PDO::FETCH_ASSOC);

            $begin = new DateTime($response["pulldate"]);
            $end = new DateTime(date("Y-m-d"));
    
            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin, $interval, $end);

            $total = 0;
            foreach ($period as $dt) {

                $j = 0;
                $output = "";

                $formatted = $dt->format("m-d-Y");
                $rawURL = $this->dataURL . $formatted . ".csv";
                $filedate = $dt->format("m-d-Y");
                $source = file_get_contents($rawURL);
                $output = "/fserver/var/www/html/Data-Analysis/COVID-19/Data/" . $formatted . ".csv";
                file_put_contents($output, $source);
                $csvFile = file($output);
                $data = [];
                if(count($csvFile)):
                    foreach ($csvFile as $line) {
                        $data[$j] = str_getcsv($line);
                        if($j != 0):
                            if($filedate < "03-01-2020"):
                                $pdoQuery = $this->_GeniSys->_secCon->prepare("
                                    INSERT IGNORE INTO covid19data (
                                        `country`,
                                        `province`,
                                        `lat`,
                                        `lng`,
                                        `confirmed`,
                                        `deaths`,
                                        `recovered`,
                                        `active`,
                                        `file`,
                                        `date`,
                                        `timeadded`
                                    )  VALUES (
                                        :country,
                                        :province,
                                        :lat,
                                        :lng,
                                        :confirmed,
                                        :deaths,
                                        :recovered,
                                        :active,
                                        :file,
                                        :date,
                                        :timeadded
                                    )
                                ");
                                $pdoQuery->execute([
                                    ":country"=>$data[$j][1],
                                    ":province"=>$data[$j][0],
                                    ":lat"=> "",
                                    ":lng"=> "",
                                    ":confirmed"=>$data[$j][3] ? $data[$j][3] : 0,
                                    ":deaths"=>$data[$j][4] ? $data[$j][4] : 0,
                                    ":recovered"=>$data[$j][5] ? $data[$j][5] : 0,
                                    ":active"=> 0,
                                    ":file"=> $output,
                                    ":date"=>date('Y-m-d h:i:s', strtotime($data[$j][2])),
                                    ":timeadded"=>time()
                                ]);
                            elseif($filedate < "03-22-2020"):
                                $pdoQuery = $this->_GeniSys->_secCon->prepare("
                                    INSERT IGNORE INTO covid19data (
                                        `country`,
                                        `province`,
                                        `lat`,
                                        `lng`,
                                        `confirmed`,
                                        `deaths`,
                                        `recovered`,
                                        `active`,
                                        `file`,
                                        `date`,
                                        `timeadded`
                                    )  VALUES (
                                        :country,
                                        :province,
                                        :lat,
                                        :lng,
                                        :confirmed,
                                        :deaths,
                                        :recovered,
                                        :active,
                                        :file,
                                        :date,
                                        :timeadded
                                    )
                                ");
                                $pdoQuery->execute([
                                    ":country"=>$data[$j][1],
                                    ":province"=>$data[$j][0],
                                    ":lat"=> $data[$j][6],
                                    ":lng"=> $data[$j][7],
                                    ":confirmed"=>$data[$j][3] ? $data[$j][3] : 0,
                                    ":deaths"=>$data[$j][4] ? $data[$j][4] : 0,
                                    ":recovered"=>$data[$j][5] ? $data[$j][5] : 0,
                                    ":active"=> 0,
                                    ":file"=> $output,
                                    ":date"=>date('Y-m-d h:i:s', strtotime($data[$j][2])),
                                    ":timeadded"=>time()
                                ]);
                            else:
                                $pdoQuery = $this->_GeniSys->_secCon->prepare("
                                    INSERT IGNORE INTO covid19data (
                                        `country`,
                                        `province`,
                                        `lat`,
                                        `lng`,
                                        `confirmed`,
                                        `deaths`,
                                        `recovered`,
                                        `active`,
                                        `file`,
                                        `date`,
                                        `timeadded`
                                    )  VALUES (
                                        :country,
                                        :province,
                                        :lat,
                                        :lng,
                                        :confirmed,
                                        :deaths,
                                        :recovered,
                                        :active,
                                        :file,
                                        :date,
                                        :timeadded
                                    )
                                ");
                                $pdoQuery->execute([
                                    ":country"=>$data[$j][3],
                                    ":province"=>$data[$j][2],
                                    ":lat"=> $data[$j][5],
                                    ":lng"=> $data[$j][6],
                                    ":confirmed"=>$data[$j][7] ? $data[$j][7] : 0,
                                    ":deaths"=>$data[$j][8] ? $data[$j][8] : 0,
                                    ":recovered"=>$data[$j][9] ? $data[$j][9] : 0,
                                    ":active"=> $data[$j][10] ? $data[$j][10] : 0,
                                    ":file"=> $output,
                                    ":date"=>date('Y-m-d h:i:s', strtotime($data[$j][4])),
                                    ":timeadded"=>time()
                                ]);
                            endif;
                        endif;
                        $j++;
                    }
                    $total = $total + $j;
    
                    $pdoQuery = $this->_GeniSys->_secCon->prepare("
                        INSERT INTO covid19pulls (
                            `pulldate`,
                            `datefrom`,
                            `dateto`,
                            `rows`
                        )  VALUES (
                            :pulldate,
                            :datefrom,
                            :dateto,
                            :rows
                        )
                    ");
                    $pdoQuery->execute([
                        ":pulldate"=>date("Y-m-d"),
                        ":datefrom"=>$response["pulldate"],
                        ":dateto"=>date("Y-m-d"),
                        ":rows"=>$total
                    ]);

                else:
                    return [
                        "Response" => "FAILED",
                        "Message" =>  "No new data currently available. "
                    ];
                endif;
    
            }

            return [
                "Response" => "OK",
                "Message" =>  "Imported " . $j . " rows of COVID-19 statistical data from " . $output
            ];

        }

    }
    
    $COVID19 = new COVID19($_GeniSys);

    if(filter_input(INPUT_POST, "updateCOVID", FILTER_SANITIZE_NUMBER_INT)):
        die(json_encode($COVID19->updateCOVID()));
    endif;