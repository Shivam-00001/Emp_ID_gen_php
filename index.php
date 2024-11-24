<?php include('db.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee ID Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <style>
        /* General styles */
        .id-card {
            width: 100%;
            max-width: 400px;
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 20px;
            background-color: #f8f9fa;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin: auto;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        .circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            overflow: hidden;
        }

        .circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .id-card h5 {
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }

        .id-card p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }

        .id-card .barcode {
            margin: 10px auto;
            max-width: 100%;
            overflow: hidden;
            text-align: center;
        }

        .btn-group {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
        }

        @media (max-width: 768px) {
            .id-card {
                max-width: 100%;
            }
        }

        /* Print-specific styles */
        @media print {
            body * {
                visibility: hidden;
            }

            .id-card, .id-card * {
                visibility: visible;
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact; /* Ensure colors print correctly */
            }

            .id-card {
                margin: 0;
                width: 400px;
                box-shadow: none;
            }

            .btn-group {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Employee ID Generator</h2>

    <!-- Form to add employee -->
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="mobile" class="form-label">Mobile Number</label>
            <input type="text" class="form-control" id="mobile" name="mobile" required>
        </div>
        <div class="mb-3">
            <label for="dob" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="dob" name="dob" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Upload Image</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
        </div>
        <button type="submit" name="generate" class="btn btn-primary w-100">Generate Employee ID</button>
    </form>

    <?php
    if (isset($_POST['generate'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $dob = $_POST['dob'];
        $imagePath = "";

        if (!empty($_FILES['image']['name'])) {
            $targetDir = "uploads/";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $imageName = uniqid() . "_" . basename($_FILES['image']['name']);
            $imagePath = $targetDir . $imageName;

            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (in_array($_FILES['image']['type'], $allowedTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                    echo "File uploaded successfully.<br>";
                } else {
                    echo "Error uploading the file.<br>";
                    $imagePath = "";
                }
            } else {
                echo "Invalid file type. Only JPEG, PNG, and GIF are allowed.<br>";
                $imagePath = "";
            }
        }

        $employeeID = "EMP" . mt_rand(100000, 999999);

        $stmt = $conn->prepare("INSERT INTO employees (employee_id, name, email, mobile, dob, image_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $employeeID, $name, $email, $mobile, $dob, $imagePath);

        if ($stmt->execute()) {
            echo "Employee created successfully. ID: " . $employeeID;
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
    ?>

    <h3 class="mt-5">Employee List</h3>

    <div class="row">
        <?php
        $result = $conn->query("SELECT * FROM employees");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="id-card">
                        <div class="circle">
                            <?php if ($row['image_path']): ?>
                                <img src="<?php echo $row['image_path']; ?>" alt="Profile Image">
                            <?php else: ?>
                                <span>No Image</span>
                            <?php endif; ?>
                        </div>
                        <h5><?php echo $row['employee_id']; ?></h5>
                        <h5><?php echo $row['name']; ?></h5>
                        <p><strong>Email:</strong> <?php echo $row['email']; ?></p>
                        <p><strong>Mobile:</strong> <?php echo $row['mobile']; ?></p>
                        <p><strong>DOB:</strong> <?php echo $row['dob']; ?></p>

                        <div class="barcode">
                            <svg id="barcode<?php echo $row['id']; ?>"></svg>
                            <script>
                                JsBarcode("#barcode<?php echo $row['id']; ?>", "<?php echo $row['employee_id']; ?>", {
                                    format: "CODE128",
                                    lineColor: "#000000",
                                    width: 2,
                                    height: 60,
                                    displayValue: true
                                });
                            </script>
                        </div>
                        </div>
                        <div class="btn-group">
                            <a href="update.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Update</a>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                            <button class="btn btn-success btn-sm" onclick="printCard('<?php echo $row['id']; ?>')">Print</button>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>No employees found.</p>";
        }
        ?>


    <script>
        function printCard(cardId) {
            const card = document.querySelector(`#barcode${cardId}`).closest('.id-card');
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = card.outerHTML;
            window.print();

            document.body.innerHTML = originalContent;
            location.reload();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
