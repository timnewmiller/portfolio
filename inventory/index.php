<!doctype html>
<html lang="en">
<?php 

    require_once 'config.php';
    // Check if 'selected' parameter exists in URL
    $selected_list_id = isset($_GET['selected']) ? $_GET['selected'] : null;
    $default_selected = '';

    // Validate the list ID exists in the database
    if ($selected_list_id) {
        $stmt = $pdo->prepare("SELECT list_id FROM lists WHERE list_id = ?");
        $stmt->execute([$selected_list_id]);
        if ($stmt->fetch()) {
            // List exists - set default selection
            $default_selected = $selected_list_id;
        }
    }
?>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>286 Digital Storage</title>
  </head>
  <body>
    <header>

    </header>
    <main class="container mt-5">
      <div class="row">
        <div class="col-12 col-md-9 col-lg-6 mx-auto">
          <h1>Inventory Tracker</h1>
        </div>
      </div>
      <div class="row">
        <div class="col-12 col-md-9 col-lg-6 mx-auto">
<?php
require_once 'config.php';
// Fetch existing lists from the database
$query = "SELECT list_id, list_name FROM lists";
$stmt = $pdo->prepare($query);
$stmt->execute();
$lists = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<form id="itemForm" class="container mt-5" action="save-data.php" method="POST">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <h2 class="card-title mb-4 col-10">Item Inventory Manager</h2>
                <a href="index.php" class="btn btn-warning btn-sm col-2 mb-4">reset</a>
            </div>
            
            <div class="row align-items-center mb-3" id="listContainer">
                <div class="col-8">
                    <select class="form-select mb-2" id="listSelect" name="listSelect" aria-label="Select an existing list" onchange="populateItems()">
                        <option selected>Choose a list...</option>
                        <?php foreach ($lists as $list): ?>
                            <option value="<?php echo htmlspecialchars($list['list_id']); ?>" 
                                <?php echo ($list['list_id'] == $default_selected) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($list['list_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                </div>
                <div class="col-4">
                    <button class="btn btn-outline-secondary w-100" type="button" onclick="addListName()">(+) New List</button>
                </div>
                 <div class="row d-none mt-3" id="newListName">
                    <div class="form-group mb-3 col-12">
                      <input type="text" class="form-control" id="listName" name="listName" placeholder="enter name" required>
                    </div>
                </div>
            </div>

            <div id="itemContainer" class="mb-3">
                <div class="item-row row my-3">
                  <div class="form-group col-8">
                    <input type="text" class="form-control" id="itemName" name="itemName[]" placeholder="item" required>
                  </div>
          
                  <div class="form-group col-2">
                    <input type="number" class="form-control" id="itemQuantity" name="itemQuantity[]" min="0" placeholder="qty">
                  </div>
                </div>
              </div>

            <div class="d-flex gap-2 mb-3">
                <button type="button" class="btn btn-primary" onclick="addItem()">(+) item</button>
                <button type="submit" class="btn btn-success">Save List</button>
            </div>
        </div>
    </div>
</form>

<script>
// Run populateItems() if default selection exists
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('listSelect').value) {
        populateItems();
    }
});

// Function to populate items based on selected list
function populateItems() {
    const select = document.getElementById('listSelect');
    const selectedListId = select.value;
    const listNameInput = document.getElementById('listName');

    // Clear previous items
    const itemContainer = document.getElementById('itemContainer');
    itemContainer.innerHTML = '';

    if (selectedListId) {
        // Fetch items for the selected list using AJAX
        fetch('get-data.php?id=' + selectedListId)
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const itemRow = document.createElement('div');
                    itemRow.className = 'item-row row my-3';
                    itemRow.innerHTML = `
                        <div class="form-group col-8">
                            <input type="text" class="form-control item-name" name="itemName[]" value="${item.item_name}" placeholder="Item Name" required>
                            <input type="hidden" name="itemId[]" value="${item.item_id}">
                            </div>
                        <div class="form-group col-2">
                            <input type="number" class="form-control item-quantity" name="itemQuantity[]" value="${item.quantity}" min="0" placeholder="Qty" required>
                        </div>`;
                    itemContainer.appendChild(itemRow);
                });
            });
            listNameInput.removeAttribute('required');
    } else {
        listNameInput.setAttribute('required','required');
    }
}
</script>
        </div>

      </div>
    </main>
    <footer>
      
    </footer>
    <script>
        function addListName() {
            const newListName = document.getElementById('newListName');
            const select = document.getElementById('listSelect');

            newListName.classList.toggle('d-none');           

            if(newListName.classList.contains('d-none')) {
                select.removeAttribute('disabled');
            } else {
                select.setAttribute('disabled','');
            }
        }

        function addItem() {
            const container = document.getElementById('itemContainer');
            const row = document.createElement('div');
            row.className = 'item-row row';
            row.innerHTML = `
                <div class="form-group mb-3 col-8">
                    <input type="text" class="form-control item-name" name="itemName[]" placeholder="item">
                </div>
                
                <div class="form-group mb-3 col-2">
                    <input type="number" class="form-control item-quantity" name="itemQuantity[]" min="0" placeholder="qty">
                </div>

                <div class="form-group col-2 mb-3">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(this)">\(-\) item</button>
                </div>
            `;
            container.appendChild(row);
        }

        function removeItem(button) {
            button.closest('.item-row').remove();
        }


    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>



  </body>
</html>
