<?php
require_once __DIR__ . "/admin_auth.php";

/* =========================
   SUMMARY STATISTICS
========================= */

// Total Sales - excluding cancelled orders
$sales_sql = "SELECT SUM(total_amount) AS total_sales
              FROM orders
              WHERE order_status != 'Cancelled'";

$sales_result = mysqli_query($conn, $sales_sql);
$sales_data = mysqli_fetch_assoc($sales_result);

$total_sales = $sales_data["total_sales"] ?? 0;


// Total Orders
$orders_sql = "SELECT COUNT(*) AS total_orders
               FROM orders";

$orders_result = mysqli_query($conn, $orders_sql);
$orders_data = mysqli_fetch_assoc($orders_result);

$total_orders = $orders_data["total_orders"] ?? 0;


// Valid Orders - excluding cancelled orders
$valid_orders_sql = "SELECT COUNT(*) AS valid_orders
                     FROM orders
                     WHERE order_status != 'Cancelled'";

$valid_orders_result = mysqli_query($conn, $valid_orders_sql);
$valid_orders_data = mysqli_fetch_assoc($valid_orders_result);

$valid_orders = $valid_orders_data["valid_orders"] ?? 0;


// Average Order Value
$average_order_value = $valid_orders > 0
    ? $total_sales / $valid_orders
    : 0;


// Completed Orders
$completed_sql = "SELECT COUNT(*) AS completed_orders
                  FROM orders
                  WHERE order_status = 'Completed'";

$completed_result = mysqli_query($conn, $completed_sql);
$completed_data = mysqli_fetch_assoc($completed_result);

$completed_orders = $completed_data["completed_orders"] ?? 0;


// Pending Orders
$pending_sql = "SELECT COUNT(*) AS pending_orders
                FROM orders
                WHERE order_status = 'Pending'";

$pending_result = mysqli_query($conn, $pending_sql);
$pending_data = mysqli_fetch_assoc($pending_result);

$pending_orders = $pending_data["pending_orders"] ?? 0;


// Cancelled Orders
$cancelled_sql = "SELECT COUNT(*) AS cancelled_orders
                  FROM orders
                  WHERE order_status = 'Cancelled'";

$cancelled_result = mysqli_query($conn, $cancelled_sql);
$cancelled_data = mysqli_fetch_assoc($cancelled_result);

$cancelled_orders = $cancelled_data["cancelled_orders"] ?? 0;


/* =========================
   MONTHLY SALES DATA
========================= */

$monthly_sales_sql = "SELECT
                        DATE_FORMAT(created_at, '%Y-%m') AS month,
                        SUM(total_amount) AS sales
                      FROM orders
                      WHERE order_status != 'Cancelled'
                      GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                      ORDER BY month ASC";

$monthly_sales_result = mysqli_query($conn, $monthly_sales_sql);

$month_labels = [];
$month_sales = [];

while ($row = mysqli_fetch_assoc($monthly_sales_result)) {

    $month_labels[] = date(
        "M Y",
        strtotime($row["month"] . "-01")
    );

    $month_sales[] = (float)$row["sales"];
}


/* =========================
   ORDER STATUS DATA
========================= */

$status_names = [
    "Pending",
    "Confirmed",
    "Preparing",
    "Ready",
    "Completed",
    "Cancelled"
];

$status_counts = [
    "Pending" => 0,
    "Confirmed" => 0,
    "Preparing" => 0,
    "Ready" => 0,
    "Completed" => 0,
    "Cancelled" => 0
];

$status_sql = "SELECT
                    order_status,
                    COUNT(*) AS total
               FROM orders
               GROUP BY order_status";

$status_result = mysqli_query($conn, $status_sql);

while ($row = mysqli_fetch_assoc($status_result)) {

    $status = $row["order_status"];

    if (isset($status_counts[$status])) {
        $status_counts[$status] = (int)$row["total"];
    }
}

$status_values = [];

foreach ($status_names as $status) {
    $status_values[] = $status_counts[$status];
}


/* =========================
   BEST-SELLING PRODUCTS
========================= */

$best_selling_sql = "
    SELECT
        p.product_id,
        p.product_name AS product_name,
        SUM(oi.Quantity) AS quantity_sold,
        SUM(oi.Quantity * oi.Price) AS total_revenue
    FROM order_items oi
    INNER JOIN products p
        ON p.product_id = oi.product_ID
    INNER JOIN orders o
        ON o.order_id = oi.Order_ID
    WHERE o.order_status != 'Cancelled'
    GROUP BY p.product_id, p.product_name
    ORDER BY quantity_sold DESC, total_revenue DESC
    LIMIT 10
";

$best_selling_result = mysqli_query($conn, $best_selling_sql);


/* =========================
   BEST-SELLING CHART DATA
========================= */

$best_selling_chart_labels = [];
$best_selling_chart_data = [];

if ($best_selling_result && mysqli_num_rows($best_selling_result) > 0) {

    mysqli_data_seek($best_selling_result, 0);

    while ($product = mysqli_fetch_assoc($best_selling_result)) {

        $best_selling_chart_labels[] = $product["product_name"];
        $best_selling_chart_data[] = (int)$product["quantity_sold"];
    }

    mysqli_data_seek($best_selling_result, 0);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Analytics Dashboard - Little Treats Bakery</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">


    <style>

        body {
            background: #f8f5f2;
        }


        .analytics-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }


        .analytics-header {
            margin-bottom: 30px;
        }


        .analytics-header h1 {
            margin-bottom: 8px;
            color: #3f2d25;
        }


        .analytics-header p {
            color: #777;
            margin: 0;
        }


        /* =========================
           SUMMARY CARDS
        ========================= */

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }


        .stat-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }


        .stat-card h3 {
            margin: 0 0 10px;
            font-size: 15px;
            color: #777;
            font-weight: 600;
        }


        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #3f2d25;
        }


        /* =========================
           CHART GRID
        ========================= */

        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 35px;
        }


        .chart-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }


        .chart-card h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #3f2d25;
            font-size: 20px;
        }


        .chart-container {
            position: relative;
            width: 100%;
            height: 350px;
        }


        /* =========================
           BEST-SELLING CHART
        ========================= */

        .best-selling-chart-container {
            position: relative;
            width: 100%;
            height: 420px;
            margin-bottom: 30px;
        }


        /* =========================
           BEST-SELLING TABLE
        ========================= */

        .table-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 35px;
            overflow-x: auto;
        }


        .table-card h2 {
            color: #3f2d25;
            margin-top: 0;
            margin-bottom: 8px;
            font-size: 20px;
        }


        .table-description {
            color: #777;
            margin: 0 0 20px;
            line-height: 1.5;
        }


        .best-selling-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }


        .best-selling-table th,
        .best-selling-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #eee5df;
            text-align: left;
        }


        .best-selling-table th {
            background: #f8f5f2;
            color: #3f2d25;
            font-size: 14px;
            font-weight: 700;
        }


        .best-selling-table td {
            color: #51443d;
            font-size: 14px;
        }


        .best-selling-table tbody tr:hover {
            background: #fffaf6;
        }


        .best-selling-table .numeric {
            text-align: right;
            white-space: nowrap;
        }


        .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #f1e3d8;
            color: #6f3d24;
            font-weight: 700;
        }


        .empty-table-message {
            text-align: center !important;
            color: #777 !important;
            padding: 24px !important;
        }


        /* =========================
           INFORMATION CARD
        ========================= */

        .info-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 30px;
        }


        .info-card h2 {
            color: #3f2d25;
            margin-top: 0;
        }


        .info-card p {
            color: #666;
            line-height: 1.6;
        }


        /* =========================
           BACK BUTTON
        ========================= */

        .back-button {
            display: inline-block;
            padding: 11px 20px;
            background: #3f2d25;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.2s ease;
        }


        .back-button:hover {
            opacity: 0.9;
        }


        /* =========================
           HEADER LOGO
        ========================= */

        .site-header {
            padding: 15px 0;
            text-align: left;
        }


        .site-header .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }


        .site-header .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }


        .site-header .logo-area img {
            width: 65px;
            height: 65px;
            max-width: 65px;
            object-fit: contain;
            flex-shrink: 0;
            background: #ffffff;
            border-radius: 10px;
            padding: 5px;
        }


        .site-header .logo-area h1 {
            font-size: 25px;
            margin: 0;
        }


        .site-header .logo-area p {
            margin: 4px 0 0;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 900px) {

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }


            .charts-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 600px) {

            .analytics-container {
                margin: 25px auto;
                padding: 0 15px;
            }


            .stats-grid {
                grid-template-columns: 1fr;
            }


            .stat-card {
                padding: 20px;
            }


            .chart-card {
                padding: 18px;
            }


            .chart-container {
                height: 280px;
            }


            .best-selling-chart-container {
                height: 500px;
            }


            .analytics-header h1 {
                font-size: 25px;
            }


            .site-header .header-inner {
                flex-wrap: wrap;
                padding: 0 15px;
            }


            .site-header .logo-area img {
                width: 50px;
                height: 50px;
                max-width: 50px;
            }


            .site-header .logo-area h1 {
                font-size: 20px;
            }

        }

    </style>

    <?php include __DIR__ . "/../includes/head_extra.php"; ?>

</head>


<body>


<!-- =========================
     HEADER
========================= -->

<?php $admin_subtitle = "Admin Analytics Dashboard"; include __DIR__ . "/../includes/admin_header.php"; ?>


<main id="main-content" class="analytics-container">


    <!-- =========================
         PAGE HEADER
    ========================= -->

    <div class="analytics-header">

        <h1>Analytics &amp; Reports</h1>

        <p>
            Monitor bakery sales, orders and overall business performance.
        </p>

    </div>


    <!-- =========================
         SUMMARY CARDS
    ========================= -->

    <div class="stats-grid">


        <div class="stat-card">

            <h3>Total Sales</h3>

            <div class="stat-value">

                Rs. <?php echo number_format($total_sales, 2); ?>

            </div>

        </div>


        <div class="stat-card">

            <h3>Total Orders</h3>

            <div class="stat-value">

                <?php echo $total_orders; ?>

            </div>

        </div>


        <div class="stat-card">

            <h3>Average Order Value</h3>

            <div class="stat-value">

                Rs. <?php echo number_format($average_order_value, 2); ?>

            </div>

        </div>


        <div class="stat-card">

            <h3>Completed Orders</h3>

            <div class="stat-value">

                <?php echo $completed_orders; ?>

            </div>

        </div>


        <div class="stat-card">

            <h3>Pending Orders</h3>

            <div class="stat-value">

                <?php echo $pending_orders; ?>

            </div>

        </div>


        <div class="stat-card">

            <h3>Cancelled Orders</h3>

            <div class="stat-value">

                <?php echo $cancelled_orders; ?>

            </div>

        </div>

    </div>


    <!-- =========================
         EXISTING CHARTS
    ========================= -->

    <div class="charts-grid">


        <!-- MONTHLY SALES CHART -->

        <div class="chart-card">

            <h2>Monthly Sales Trend</h2>

            <div class="chart-container">

                <canvas id="salesChart"></canvas>

            </div>

        </div>


        <!-- ORDER STATUS CHART -->

        <div class="chart-card">

            <h2>Order Status Distribution</h2>

            <div class="chart-container">

                <canvas id="statusChart"></canvas>

            </div>

        </div>

    </div>


    <!-- =========================
         BEST-SELLING PRODUCTS
    ========================= -->

    <section class="table-card">

        <h2>Top 10 Best-Selling Products</h2>

        <p class="table-description">
            Products ranked by quantity sold. The chart provides a quick visual comparison of product demand.
        </p>


        <!-- BEST-SELLING BAR CHART -->

        <div class="best-selling-chart-container">

            <canvas id="bestSellingChart"></canvas>

        </div>


        <!-- BEST-SELLING TABLE -->

        <h2>Best-Selling Products Details</h2>

        <p class="table-description">
            Products ranked by quantity sold. Revenue excludes cancelled orders.
        </p>


        <table class="best-selling-table">

            <thead>

                <tr>

                    <th>Rank</th>

                    <th>Product Name</th>

                    <th class="numeric">
                        Quantity Sold
                    </th>

                    <th class="numeric">
                        Total Revenue
                    </th>

                </tr>

            </thead>


            <tbody>

                <?php if ($best_selling_result && mysqli_num_rows($best_selling_result) > 0): ?>

                    <?php $rank = 1; ?>

                    <?php while ($product = mysqli_fetch_assoc($best_selling_result)): ?>

                        <tr>

                            <td>

                                <span class="rank-badge">

                                    <?php echo $rank++; ?>

                                </span>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $product["product_name"]
                                );

                                ?>

                            </td>


                            <td class="numeric">

                                <?php

                                echo (int)$product["quantity_sold"];

                                ?>

                            </td>


                            <td class="numeric">

                                Rs.

                                <?php

                                echo number_format(
                                    (float)$product["total_revenue"],
                                    2
                                );

                                ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="4"
                            class="empty-table-message">

                            No product sales data is available yet.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </section>


    <!-- =========================
         INFORMATION
    ========================= -->

    <div class="info-card">

        <h2>Business Performance Overview</h2>

        <p>

            The analytics dashboard provides a centralized view of
            sales and order performance. The monthly sales chart helps
            identify changes in sales activity over time, while the
            order status chart provides an overview of the current
            distribution of customer orders.

        </p>


        <p>

            The best-selling products section identifies the products
            with the highest sales quantities and total revenue.
            The visual chart makes it easier to compare product demand,
            while the detailed table provides exact sales figures.
            This information can help the bakery understand customer
            demand and make better decisions about product planning
            and stock management.

        </p>

    </div>


    <!-- =========================
         BACK BUTTON
    ========================= -->

    <a href="dashboard.php"
       class="back-button">

        ← Back to Dashboard

    </a>


</main>


<!-- =========================
     CHART.JS
========================= -->

<script src="../assets/js/chart.umd.js"></script>


<script>

    /* =========================
       MONTHLY SALES CHART
    ========================= */

    const salesLabels =
        <?php echo json_encode($month_labels); ?>;

    const salesData =
        <?php echo json_encode($month_sales); ?>;


    const salesCtx =
        document.getElementById("salesChart");


    new Chart(salesCtx, {

        type: "line",

        data: {

            labels: salesLabels,

            datasets: [{

                label: "Sales",

                data: salesData,

                borderWidth: 3,

                fill: false,

                tension: 0.3

            }]

        },


        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {

                    display: true

                }

            },


            scales: {

                y: {

                    beginAtZero: true,

                    ticks: {

                        callback: function(value) {

                            return "Rs. " + value;

                        }

                    }

                }

            }

        }

    });



    /* =========================
       ORDER STATUS CHART
    ========================= */

    const statusLabels =
        <?php echo json_encode($status_names); ?>;

    const statusData =
        <?php echo json_encode($status_values); ?>;


    const statusCtx =
        document.getElementById("statusChart");


    new Chart(statusCtx, {

        type: "doughnut",

        data: {

            labels: statusLabels,

            datasets: [{

                label: "Orders",

                data: statusData,

                borderWidth: 2

            }]

        },


        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {

                    position: "bottom"

                }

            }

        }

    });



    /* =========================
       BEST-SELLING PRODUCTS CHART
    ========================= */

    const bestSellingLabels =
        <?php echo json_encode($best_selling_chart_labels); ?>;

    const bestSellingData =
        <?php echo json_encode($best_selling_chart_data); ?>;


    const bestSellingCtx =
        document.getElementById("bestSellingChart");


    new Chart(bestSellingCtx, {

        type: "bar",

        data: {

            labels: bestSellingLabels,

            datasets: [{

                label: "Quantity Sold",

                data: bestSellingData,

                borderWidth: 1

            }]

        },


        options: {

            indexAxis: "y",

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {

                    display: true

                }

            },


            scales: {

                x: {

                    beginAtZero: true,

                    ticks: {

                        precision: 0

                    }

                },

                y: {

                    ticks: {

                        autoSkip: false

                    }

                }

            }

        }

    });

</script>


</body>

</html>