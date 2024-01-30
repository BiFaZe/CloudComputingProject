// ------------------- My code -------------------------------
document.getElementById('signup-button').addEventListener('click', function() {
    location.href='https://gamelibrary-auth.auth.us-east-1.amazoncognito.com/login?client_id=3jb1gq0bsa2krc57e0ttcgr26j&response_type=code&scope=openid&redirect_uri=https%3A%2F%2Fchivalrous-sapphire-donkey.glitch.me%2F';
});


window.onload = function () {
  AWS.config.update({ region: "us-east-1" });
  AWS.config.region = "us-east-1";
  AWS.config.credentials = new AWS.CognitoIdentityCredentials({
    IdentityPoolId: "us-east-1:cd82df96-1cd3-456e-831d-42736979acfc",
  });
  AWS.config.credentials.get(function () {
    var accessKeyId = AWS.config.credentials.accessKeyId;
    var secretAccessKey = AWS.config.credentials.secretAccessKey;
    var sessionToken = AWS.config.credentials.sessionToken;
  });
  var ddb = new AWS.DynamoDB.DocumentClient({ apiVersion: "2012-08-10" });
  var params = {
    TableName: "MobyGameDataset",
  };

  document.getElementById("loading").style.display = "block";
  var contentDiv = document.getElementById("content");
  contentDiv.classList.add("loading");
  setTimeout(function () {
    ddb.scan(params, function (err, data) {
      if (err) {
        console.log("Error", err);
      } else {
        console.log("Success", data.Items);
        let allGames = data.Items;
        let gridContainer = document.querySelector(".grid-container");

        function updateGridItems(gamesToShow, layout) {
          gridContainer.className = "grid-container " + layout;

          let html = gamesToShow.map(
            (game) => `
          <div class="grid-item" data-title="${game["Game Title"]}">
            <div class="game-image">
              <img src="${game.Image}" alt="${game.Console}" />
            </div>
            <div class="game-title">
              <p class="game-name">${game["Game Title"]}</p>
              <p class="game-price">${
                game["Price (Best)"]
                  ? game["Price (Best)"] + "$"
                  : "Unavailable"
              }</p>
            </div>
          </div>
        `
          );

          gridContainer.innerHTML = html.join("");

          document.querySelectorAll(".grid-item").forEach((item) => {
            item.addEventListener("click", function (e) {
              e.preventDefault();
              let title = this.dataset.title;
              let game = allGames.find((game) => game["Game Title"] === title);
              gridContainer.innerHTML = `
              <button id="back-button">Back</button>
              <div class="game-details">
                <div class="game-content">
                  <h1>${game["Game Title"]}</h1>
                  <img src="${game.Image}" alt="${game.Console}" />
                </div>
                <div class="game-info">
                  <p class= "game-price"><strong>Price:</strong> ${
                    game["Price (Best)"]
                      ? game["Price (Best)"] + "$"
                      : "Unavailable"
                  }</p>
                  <p class="game-description"><strong>Description:</strong>\n${
                    game.Description
                  }</p>  
                </div>
              </div>
            `;
              gridContainer.className = "grid-container details-layout";

              document
                .getElementById("back-button")
                .addEventListener("click", function (e) {
                  e.preventDefault();
                  updateGridItems(allGames, "home-layout");
                });
            });
          });
        }

        updateGridItems(allGames, "home-layout");

        document
          .getElementById("search-button")
          .addEventListener("click", function (e) {
            e.preventDefault();
            let searchValue = document
              .getElementById("search-input")
              .value.toLowerCase();

            let filteredGames = allGames.filter((game) =>
              game["Game Title"].toLowerCase().includes(searchValue)
            );
            if (filteredGames.length > 0) {
              updateGridItems(filteredGames, "home-layout");
              document.getElementById("no-results").style.display = "none";
            } else {
              gridContainer.innerHTML = "";
              document.getElementById("no-results").style.display = "block";
            }
          });
        document.getElementById("loading").style.display = "none";
      }
      contentDiv.classList.remove("loading");
      contentDiv.classList.add("loaded");
    });
  }, 1500);
};