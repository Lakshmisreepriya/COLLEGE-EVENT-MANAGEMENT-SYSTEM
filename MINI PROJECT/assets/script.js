// Global variables
let currentTab = "dashboard"

// Initialize the application
document.addEventListener("DOMContentLoaded", () => {
  initializeApp()
})

function initializeApp() {
  // Initialize navigation
  initializeNavigation()

  // Load initial content
  if (currentTab === "dashboard") {
    loadDashboard()
  }

  // Initialize modals
  initializeModals()
}

function initializeNavigation() {
  const navTabs = document.querySelectorAll(".nav-tab")
  navTabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      const tabName = this.getAttribute("data-tab")
      switchTab(tabName)
    })
  })
}

function switchTab(tabName) {
  // Update active tab
  document.querySelectorAll(".nav-tab").forEach((tab) => {
    tab.classList.remove("active")
  })
  document.querySelector(`[data-tab="${tabName}"]`).classList.add("active")

  // Hide all content sections
  document.querySelectorAll(".tab-content").forEach((content) => {
    content.classList.add("hidden")
  })

  // Show selected content
  document.getElementById(tabName).classList.remove("hidden")

  currentTab = tabName

  // Load content based on tab
  switch (tabName) {
    case "dashboard":
      loadDashboard()
      break
    case "events":
      loadEvents()
      break
    case "polls":
      loadPolls()
      break
  }
}

function loadDashboard() {
  if (document.getElementById("dashboard-stats")) {
    fetch("api/dashboard_stats.php")
      .then((response) => response.json())
      .then((data) => {
        updateDashboardStats(data)
      })
      .catch((error) => console.error("Error loading dashboard:", error))
  }
}

function updateDashboardStats(data) {
  const statsContainer = document.getElementById("dashboard-stats")
  if (statsContainer) {
    statsContainer.innerHTML = `
            <div class="stat-card">
                <div class="stat-number">${data.total_events || 0}</div>
                <div class="stat-label">Total Events</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">${data.total_polls || 0}</div>
                <div class="stat-label">Active Polls</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">${data.total_registrations || 0}</div>
                <div class="stat-label">Event Registrations</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">${data.total_votes || 0}</div>
                <div class="stat-label">Poll Votes</div>
            </div>
        `
  }
}

function loadEvents() {
  fetch("api/events.php")
    .then((response) => response.json())
    .then((data) => {
      displayEvents(data)
    })
    .catch((error) => console.error("Error loading events:", error))
}

function displayEvents(events) {
  const eventsContainer = document.getElementById("events-list")
  if (!eventsContainer) return

  if (events.length === 0) {
    eventsContainer.innerHTML = "<p>No events available.</p>"
    return
  }

  eventsContainer.innerHTML = events
    .map(
      (event) => `
        <div class="event-card">
            <div class="event-card-header">
                <div class="event-title">${event.title}</div>
                <div class="event-date">${formatDate(event.event_date)}</div>
            </div>
            <div class="event-card-body">
                <div class="event-description">${event.description}</div>
                <div class="event-location">📍 ${event.location}</div>
                <div class="event-participants">👥 Max: ${event.max_participants}</div>
                ${
                  event.is_registered
                    ? '<button class="btn btn-success" disabled>Registered</button>'
                    : `<button class="btn" onclick="registerForEvent(${event.id})">Register</button>`
                }
                ${
                  window.userRole === "admin"
                    ? `
                    <button class="btn btn-danger" onclick="deleteEvent(${event.id})">Delete</button>
                `
                    : ""
                }
            </div>
        </div>
    `,
    )
    .join("")
}

function loadPolls() {
  fetch("api/polls.php")
    .then((response) => response.json())
    .then((data) => {
      displayPolls(data)
    })
    .catch((error) => console.error("Error loading polls:", error))
}

function displayPolls(polls) {
  const pollsContainer = document.getElementById("polls-list")
  if (!pollsContainer) return

  if (polls.length === 0) {
    pollsContainer.innerHTML = "<p>No polls available.</p>"
    return
  }

  pollsContainer.innerHTML = polls
    .map(
      (poll) => `
        <div class="poll-card">
            <div class="poll-title">${poll.title}</div>
            <div class="poll-description">${poll.description}</div>
            ${poll.has_voted ? displayPollResults(poll) : displayPollOptions(poll)}
            ${
              window.userRole === "admin"
                ? `
                <button class="btn btn-danger" onclick="deletePoll(${poll.id})">Delete</button>
            `
                : ""
            }
        </div>
    `,
    )
    .join("")
}

function displayPollOptions(poll) {
  return `
        <form onsubmit="submitVote(event, ${poll.id})">
            ${poll.options
              .map(
                (option) => `
                <div class="poll-option">
                    <input type="radio" name="option" value="${option.id}" id="option_${option.id}">
                    <label for="option_${option.id}">${option.option_text}</label>
                </div>
            `,
              )
              .join("")}
            <button type="submit" class="btn">Vote</button>
        </form>
    `
}

function displayPollResults(poll) {
  const totalVotes = poll.options.reduce((sum, option) => sum + option.votes, 0)

  return `
        <div class="poll-results">
            <h4>Results (${totalVotes} votes)</h4>
            ${poll.options
              .map((option) => {
                const percentage = totalVotes > 0 ? (option.votes / totalVotes) * 100 : 0
                return `
                    <div class="poll-result-item">
                        <div>${option.option_text}</div>
                        <div class="poll-result-bar">
                            <div class="poll-result-fill" style="width: ${percentage}%"></div>
                            <div class="poll-result-text">${option.votes} votes (${percentage.toFixed(1)}%)</div>
                        </div>
                    </div>
                `
              })
              .join("")}
        </div>
    `
}

function registerForEvent(eventId) {
  fetch("api/register_event.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ event_id: eventId }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("Successfully registered for event!", "success")
        loadEvents() // Reload events
      } else {
        showAlert(data.message || "Registration failed", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showAlert("Registration failed", "error")
    })
}

function submitVote(event, pollId) {
  event.preventDefault()

  const formData = new FormData(event.target)
  const optionId = formData.get("option")

  if (!optionId) {
    showAlert("Please select an option", "error")
    return
  }

  fetch("api/vote.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      poll_id: pollId,
      option_id: optionId,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("Vote submitted successfully!", "success")
        loadPolls() // Reload polls
      } else {
        showAlert(data.message || "Vote submission failed", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showAlert("Vote submission failed", "error")
    })
}

function deleteEvent(eventId) {
  if (!confirm("Are you sure you want to delete this event?")) {
    return
  }

  fetch("api/delete_event.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ event_id: eventId }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("Event deleted successfully!", "success")
        loadEvents()
      } else {
        showAlert(data.message || "Delete failed", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showAlert("Delete failed", "error")
    })
}

function deletePoll(pollId) {
  if (!confirm("Are you sure you want to delete this poll?")) {
    return
  }

  fetch("api/delete_poll.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ poll_id: pollId }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("Poll deleted successfully!", "success")
        loadPolls()
      } else {
        showAlert(data.message || "Delete failed", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showAlert("Delete failed", "error")
    })
}

function initializeModals() {
  // Close modal when clicking outside
  window.addEventListener("click", (event) => {
    const modals = document.querySelectorAll(".modal")
    modals.forEach((modal) => {
      if (event.target === modal) {
        modal.style.display = "none"
      }
    })
  })

  // Close modal when clicking close button
  document.querySelectorAll(".close").forEach((closeBtn) => {
    closeBtn.addEventListener("click", function () {
      this.closest(".modal").style.display = "none"
    })
  })
}

function showModal(modalId) {
  document.getElementById(modalId).style.display = "block"
}

function hideModal(modalId) {
  document.getElementById(modalId).style.display = "none"
}

function showAlert(message, type) {
  const alertDiv = document.createElement("div")
  alertDiv.className = `alert alert-${type}`
  alertDiv.textContent = message

  const container = document.querySelector(".container")
  container.insertBefore(alertDiv, container.firstChild)

  setTimeout(() => {
    alertDiv.remove()
  }, 5000)
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString() + " " + date.toLocaleTimeString()
}

function logout() {
  if (confirm("Are you sure you want to logout?")) {
    window.location.href = "logout.php"
  }
}
