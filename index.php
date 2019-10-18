<?php

// Author = Umer Mughal
// Last time Updated= 18-10-2019
// Sales Representation index.php file

$servername = "localhost";
$username = "root";
$password = "";
// Create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// variable check
if (!isset($_POST['File'])) $_POST['File'] = '';
if (!isset($_POST['Customer_Name'])) $_POST['Customer_Name']='';
if (!isset($_POST['Book_Name'])) $_POST['Book_Name']= '';
if (!isset($_POST['Price'])) $_POST['Price']='';

?>
    <!-- Form Creation  for filter -->
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <fieldset>
            <legend>Filter:</legend>
            Customer Name:
            <input type="text" name="Customer_Name" value=""><br><br>
            Book Name:
            <input type="text" name="Book_Name" value=""><br><br>
            Price:
            <input type="text" name="Price" value=""><br><br>
            <input type="submit" value="Submit">
        </fieldset>
    </form>
    <!-- Form to load json file -->
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="hidden" name="File" value="File">
        <input type="submit" value="Load File">
    </form>
<?php


$filter = "WHERE ";
$f_set = 0;

// if User Click to recpactive button to load file
if($_POST['File']){
    $jsonFile="Sales.json";
    $jsondata = file_get_contents($jsonFile);
    $data = json_decode($jsondata, true);


    foreach ($data as $d) {
        foreach ($d as $row) {
            $sqlq1 = "INSERT INTO sales.Customers (Customer_Name) VALUES ('" . $row["Customer_name"] . "')";
            $exec1 = $conn->query($sqlq1)or die("Query Error");
            $sqlq2 = "INSERT INTO sales.Books (Book_Name,Price) VALUES ('" . $row["Book_name"]. "', '" .$row["Price"] . "')";
            $exec2 = $conn->query($sqlq2)or die("Query Error");

            $C_DBQ = $conn->query("SELECT * FROM sales.Customers WHERE Customer_Name LIKE  '".'%'. $row["Customer_name"].'%'."'")or die("Query Error");
            $C_DB = $C_DBQ->fetch_assoc();
            $C_ID=$C_DB['Customer_ID'];

            $B_DBQ = $conn->query("SELECT * FROM sales.Books WHERE Book_Name LIKE '".'%'. $row["Book_name"].'%'."'")or die("Query Error");
            $B_DB = $B_DBQ->fetch_assoc();
            $B_ID=$B_DB['Book_ID'];

            $sqlq3 = "INSERT INTO sales.Orders (Customer_ID,Book_ID) VALUES ('" . $C_ID . "', '" . $B_ID . "')";
            $exec3 = $conn->query($sqlq3) or die("Query Error");
        }
    }
}
// Filter formulation
elseif ($_POST['Customer_Name'] != '' or $_POST['Book_Name'] != '' or $_POST['Price'] != '') {
    if ($_POST['Customer_Name'] != '') {
        $f_set=1;
        $C_DBQ = $conn->query("SELECT * FROM sales.Customers WHERE Customer_Name LIKE  '".'%'. $_POST['Customer_Name'].'%'."'")or die("Query Error");
        $C_DB = $C_DBQ->fetch_assoc();
        $C_ID=$C_DB['Customer_ID'];
        $filter .= " Customer_ID = '" . $C_ID. "'";
    }

    if ($_POST['Book_Name'] != '') {
        if ($f_set) $filter .= ' AND ';
        $B_DBQ = $conn->query("SELECT * FROM sales.Books WHERE Book_Name LIKE '".'%'. $_POST['Book_Name'].'%'."'")or die("Query Error");
        $B_DB = $B_DBQ->fetch_assoc();
        $B_ID=$B_DB['Book_ID'];
        $filter .= " Book_ID ='" . $B_ID. "'";
        $f_set=1;
    }

    if ($_POST['Price'] != '') {
        if ($f_set) $filter .= ' AND ';
        $P_DBQ = $conn->query("SELECT * FROM sales.Books WHERE Price LIKE '".'%'. $_POST['Price'].'%'."'")or die("Query Error");
        $P_DB = $P_DBQ->fetch_assoc();
        $B_ID=$P_DB['Book_ID'];
        $filter .= " Book_ID ='" . $B_ID. "'";
        $f_set=1;
    }

}
if ($f_set == 0) $filter .= ' 1';

// Dispaly Data on the same page
$sql = "SELECT * FROM sales.orders ".$filter;
$result = $conn->query($sql) or die("Query Error");
if($result){
    if (!empty($result) && $result->num_rows > 0) {
        // output data of each row
        echo '<table>';
        echo '<tr>';
        echo'<th>Customer Name</th>
             <th>Books Name </th>
             <th>Price </th>';
        echo'<tr>';

        $Total_Price=0;
        while($row = $result->fetch_assoc())  {
            $Book_DBQ = $conn->query("SELECT * FROM sales.Books WHERE Book_ID= '". $row['Book_ID']."'") or die("Query Error");
            $Book_DB = $Book_DBQ->fetch_assoc();
            $Book_name=$Book_DB['Book_Name'];
            $Book_price=$Book_DB['Price'];


            $Customer_DBQ = $conn->query("SELECT * FROM sales.Customers WHERE Customer_ID= '". $row['Customer_ID']."'")or die("Query Error");
            $Customer_DB = $Customer_DBQ->fetch_assoc();
            $Customer_name=$Customer_DB['Customer_Name'];


            echo '<tr>';
            echo '  <td>' . $Customer_name . '</td>';
            echo '  <td>' . $Book_name . '</td>';
            echo '  <td>' .  $Book_price . '</td>';
            echo '  </tr> ';
            $Total_Price= $Total_Price+$Book_price;
        }
        echo '<tr>';
        echo '  <td><br>Total Price</td>';
        echo '  <td><br></td>';
        echo '  <td><br>' . $Total_Price . '</td>';
        echo '  </tr> ';
        echo'</table>';
    }
    else {
        echo "<br> No Record Found to display";
    }
}else {
    echo "<br> Database error.";
}
?>