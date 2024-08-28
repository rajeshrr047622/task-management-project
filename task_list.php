<?php
$servername = "localhost";
$username = "root";
$password = "Rajesh@122";
$dbname = "task_manager";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variable
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_task'])) {
        $id = intval($_POST['id']);
        if ($id <= 0) {
            $message = 'Invalid task ID.';
        } else {
            $sql = "DELETE FROM tasks WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                $message = 'Task deleted successfully.';
            } else {
                $message = 'Error deleting task: ' . $conn->error;
            }
        }

        // Redirect to the same page with a message
        header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message));
        exit();
    }
}

// Fetch tasks
$sql = "SELECT * FROM tasks";
$result = $conn->query($sql);
$tasks = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();

// Check for message in URL
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List</title>
    <style>
        /* General Body and Layout */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #e0e0e0; /* Light gray background */
        }

        .container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff; /* White background for container */
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2); /* Soft shadow */
        }

        h1 {
            color: #333;
            font-size: 2.5em;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
        }

        /* Button Styling */
        .create-task-button {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            transition: background-color 0.3s, transform 0.2s;
        }

        .create-task-button:hover {
            background-color: #218838;
            transform: scale(1.05); /* Slight scale effect */
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: #ffffff;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f5f5f5; /* Light gray for alternating rows */
        }

        tr:hover {
            background-color: #e9ecef; /* Light blue-gray on hover */
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        /* Action Button Styling */
        button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            color: white;
        }

        button:hover {
            transform: scale(1.05); /* Slight scale effect */
        }

        .edit-button {
            background-color: #007bff;
        }

        .edit-button:hover {
            background-color: #0056b3;
        }

        .delete-button {
            background-color: #dc3545;
        }

        .delete-button:hover {
            background-color: #c82333;
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5); /* Semi-transparent background */
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 5px;
            width: 80%;
            max-width: 500px;
        }

        .modal-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .modal-footer {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: right;
        }

        .modal-close {
            float: right;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-close:hover {
            color: red;
        }

        .modal-button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 5px;
        }

        .modal-confirm {
            background-color: #28a745;
            color: white;
        }

        .modal-confirm:hover {
            background-color: #218838;
        }

        .modal-cancel {
            background-color: #dc3545;
            color: white;
        }

        .modal-cancel:hover {
            background-color: #c82333;
        }

        /* Message Styling */
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Task List</h1>

        <!-- Create New Task Button -->
        <a href="index.php" class="create-task-button">Create New Task</a>

        <!-- Display Message -->
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        

        <!-- Task List Table -->
        <div id="task-list">
            <?php if ($tasks): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($task['id']); ?></td>
                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                <td><?php echo htmlspecialchars($task['description']); ?></td>
                                <td><?php echo htmlspecialchars($task['status']); ?></td>
                                <td class="actions">
                                    <form method="GET" action="edit_task.php">
                                        <input type="hidden" name="edit" value="<?php echo $task['id']; ?>">
                                        <button type="submit" class="edit-button">Edit</button>
                                    </form>
                                    <button type="button" class="delete-button" onclick="openModal(<?php echo $task['id']; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No tasks found.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-close" onclick="closeModal()">&times;</span>
                <h2>Confirm Deletion</h2>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this task?</p>
            </div>
            <div class="modal-footer">
                <button class="modal-button modal-confirm" onclick="confirmDeletion()">Yes, Delete</button>
                <button class="modal-button modal-cancel" onclick="closeModal()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        let taskIdToDelete = null;

        function openModal(id) {
            taskIdToDelete = id;
            document.getElementById('confirmationModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('confirmationModal').style.display = 'none';
            taskIdToDelete = null;
        }

        function confirmDeletion() {
            if (taskIdToDelete !== null) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = taskIdToDelete;
                form.appendChild(input);
                const submitInput = document.createElement('input');
                submitInput.type = 'hidden';
                submitInput.name = 'delete_task';
                form.appendChild(submitInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
