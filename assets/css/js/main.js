// Main JavaScript functions for MealPrep

// Show loading spinner
function showLoading() {
  Swal.fire({
    title: "Loading...",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading()
    },
  })
}

// Hide loading spinner
function hideLoading() {
  Swal.close()
}

// Format currency
function formatCurrency(amount) {
  return "$" + Number.parseFloat(amount).toFixed(2)
}

// Confirm delete action
function confirmDelete(message = "You won't be able to revert this!") {
  return Swal.fire({
    title: "Are you sure?",
    text: message,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes, delete it!",
  })
}

// Show success message
function showSuccess(message) {
  Swal.fire({
    title: "Success!",
    text: message,
    icon: "success",
    confirmButtonText: "OK",
  })
}

// Show error message
function showError(message) {
  Swal.fire({
    title: "Error!",
    text: message,
    icon: "error",
    confirmButtonText: "OK",
  })
}

// Auto-hide alerts after 5 seconds
document.addEventListener("DOMContentLoaded", () => {
  const alerts = document.querySelectorAll(".alert")
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.style.opacity = "0"
      setTimeout(() => {
        alert.remove()
      }, 300)
    }, 5000)
  })
})
