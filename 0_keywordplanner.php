<?php

/* 
 *  Mail : subinpvasu@gmail.com
 *  Skype : subinpvasu 
 *  Author : SUBIN P VASU, Freelance Google Ads(AdWords) API Developer - PHP, Python
 *  Created On : Aug 24, 2021 
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

ob_start();
require_once 'Credentials.php';
require_once 'Adwords.php';


class Processor {
    protected $advertising;
	
	
    public function __construct()
    {           
        $arr = [
            'OAUTH2' => [
                            'developerToken' => Credentials::$DEVELOPER_TOKEN,
                            'clientId' => Credentials::$CLIENT_ID,
                            'clientSecret' => Credentials::$CLIENT_SECRET,
                            'refreshToken' => Credentials::$REFRESH_TOKEN,
                        ]
            ];
            $this->advertising = new Adwords($arr,  Credentials::$MASTER_ID);
    }
   
    public function get_customer_account()
    {
       return $this->advertising->GetAccountInfo($this->advertising->createClient(Credentials::$MASTER_ID), Credentials::$ACCOUNT_ID);
    }
    public function generate_keyword_ideas($keywords, $country, $language)
    {        
       return $this->advertising->KeywordMetrics($this->advertising->createClient(Credentials::$MASTER_ID), Credentials::$ACCOUNT_ID, $country, $language, $keywords, null);
    }
   
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Keyword Bids</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
       <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>  
  <script>$(document).ready(function () {
    $('#myTable').DataTable({
        order: [[2, 'desc']],
        "searching": false, 
   "paging": false,
   "info": false,
        
    });
});
</script>
  
  <style>
      .form-select
      {
          border:none;
          text-align: left;
      }
  </style>
</head>
<body>

<div class="container mt-3">
<?php
if(isset($_POST['generate']))
{
    $start = time();
    $countries = json_decode($_POST['countries'], 1);
    $ads = new Processor();
    $keywords = $_POST['keywords'];
    $language = $_POST['language'];
    $country = $_POST['country'];
    $ideas = $ads->generate_keyword_ideas([$keywords], [$country], $language);
    
    
   ?>

    
  <h2>Keyword Bids - <?php echo $ideas[0]['text'] ?></h2>
  <table class="table" id="myTable">
    <thead>
      <tr>
        <th>Keyword</th>
        <th>Competition</th>
        <th>Average Monthly Searches</th>
        <th>Competition Index</th>
        <th>Low Top Page Bid</th>
        <th>High Top Page Bid</th>
      </tr>
    </thead>
    <tbody>
     <?php 
     
     $i = 0;
        foreach ($ideas as $idea)
        {
            $i++;
            
            ?>
            <tr>
            <td><?php echo $idea['text'] ?></td>
            <td><?php echo $idea['competition'] ?></td>
            <td><?php echo $idea['avg_monthly_searches'] ?></td>
            <td><?php echo $idea['competition_index'] ?></td>            
            <td><?php echo $idea['low_top_of_page_bid_micros'] ?></td>            
            <td><?php echo $idea['high_top_of_page_bid_micros'] ?></td>            
            </tr>
            <?php
            
        }     
     ?>
    </tbody>
  </table> 
 
   <?php
}
else
{
    


?>


  <h2>Keyword Ideas</h2>
  
  <form action="" method="post">
    <div class="mb-3 mt-3">
      <label for="comment">Keyword:</label>
      <input class="form-control" name="keywords" style="height:45px;"/>
    </div>
    <div class="mb-3 mt-3">
      <label for="comment">Language:</label>
      <select class="form-control" name="language" id="select_box">
          <option value="1000">English</option>
          <option value="1003">Spanish</option>           
      </select>
    </div>
    <div class="mb-3 mt-3">
      <label for="comment">Country:</label>
      <select class="form-control" name="country" id="select_box">
          <option value="">Select</option>
           <?php
  $i=0;
  $contries = [];
  $file = fopen("countries.csv","r");
while(! feof($file))
  {
    if($i==0)
    {
        $i++;
        continue;
    }  
    $line = fgetcsv($file);
    $selected = '';
    if($line[0]==2724)
    {
        $selected = ' selected ';
    }
      echo '<option '.$selected.' value="'.$line[0].'">'.$line[1].'</option>';
      $contries[$line[0]] = $line[1];
  }
fclose($file);
  ?>
      </select>
    </div>
      <input type="hidden" name="countries" value="<?php echo htmlspecialchars(json_encode($contries)); ?>"/>
      <button type="submit" name="generate" class="btn btn-primary">Generate</button>
  </form>



<?php 
}
?>

  </div>


</body>
</html>