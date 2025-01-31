const API_URL = 'inventory_api.php';

// Fetch and display inventory items
async function fetchInventory() {
  const response = await fetch(API_URL);
  const items = await response.json();
  
  const tableBody = document.querySelector('#inventory tbody');
  tableBody.innerHTML = '';
  
  items.forEach(item => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${item.description}</td>
      <td>${item.dc_drc_lb}</td>
      <td>${item.environment}</td>
      <td>${item.url}</td>
      <td>${item.ip_address}</td>
      <td>${item.protocol}</td>
      <td>${item.port}</td>
      <td>${item.username}</td>
      <td>${item.password}</td>
      <td>${item.new_ip}</td>
      <td>${item.new_port}</td>
      <td>${item.remarks}</td>
      <td>${item.serial_number}</td>
      <td>
        <button class="edit" data-id="${item.id}">Edit</button>
        <button class="delete" data-id="${item.id}">Delete</button>
      </td>
    `;
    tableBody.appendChild(row);
  });
}

// Add new inventory item
async function addInventory(item) {
  const response = await fetch(API_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(item)
  });
  const newItem = await response.json();
  fetchInventory();
}

// Update inventory item
async function updateInventory(item) {
  await fetch(API_URL, {
    method: 'PUT', 
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(item)
  });
  fetchInventory();
}

// Delete inventory item
async function deleteInventory(id) {
  await fetch(`${API_URL}?id=${id}`, { method: 'DELETE' });
  fetchInventory();
}

// Attach event listeners
const inventoryForm = document.querySelector('#inventory-form');
const cancelBtn = document.querySelector('#cancel-btn');

// Populate form fields
function populateForm(item) {
  for (const key in item) {
    const field = inventoryForm.elements[key];
    if (field) field.value = item[key];
  }
}

// Get form data
function getFormData() {
  const formData = new FormData(inventoryForm);
  return Object.fromEntries(formData.entries());
}

// Show/hide form
function toggleForm(show) {
  inventoryForm.reset();
  inventoryForm.style.display = show ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', () => {
  fetchInventory();
  
  // Show form for new item
  document.querySelector('#add-btn').addEventListener('click', () => {
    toggleForm(true);
  });

  // Hide form on cancel
  cancelBtn.addEventListener('click', () => {
    toggleForm(false);
  });
  
  // Save new/updated item
  inventoryForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    const item = getFormData();
    
    if (item.id) {
      await updateInventory(item);
    } else {
      await addInventory(item);
    }
    
    toggleForm(false);
  });
  
  // Show form to edit item
  
  document.querySelector('#inventory').addEventListener('click', event => {
    if (event.target.matches('button.edit')) {
      // TODO: Show edit form and populate with item data
    } else if (event.target.matches('button.delete')) {
      const id = event.target.dataset.id;
      deleteInventory(id);
    }
  });
});