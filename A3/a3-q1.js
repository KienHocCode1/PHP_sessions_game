// Add event listeners to the buttons


document.getElementById("Reset").addEventListener("click", function() {
    fetchData("0");
});

document.getElementById("Play").addEventListener("click", function() {
    fetchData("1");
});


fetch( "me.php" )
.then( response => {
    if ( !response.ok ) {
        throw new Error(response.status+" "+response.statusText)
    } else {
        return response.text();
    } 
} )
.then( data => document.getElementById( "student_info" ).innerHTML = data )
.catch( error => document.getElementById( "student_info" ).innerHTML = '<strong>'+error+'</strong>' );

// Check if a hidden element with a specific ID exists
function elementExists(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        return true;
    }
    return false;
}

// Function to send a fetch request to data_processor.php
function fetchData(choice) {
    // Get the selected player_move
    const move = document.querySelector('input[name="player_move"]:checked').value;
    // get the selected difficulty
    const diff = document.querySelector('input[name="diff"]:checked').value;


    // Build the URL with the selected parameters
    const url = `nim.php?mode=${choice}&difficulty=${diff}&player_move=${move}`;

    // Fetch data from data_processor.php
    fetch(url)
        .then(response => response.json())
        .then(data => {
            // Clear previous results
            const resultContainer = document.getElementById("resultContainer");
            resultContainer.innerHTML = "";
            const winner = data["winner"];
            const stones = data["stones"];
            const move = data["move"];
            const player = data["player"];
            const start = stones + move;
            resultContainer.innerHTML = `The winner is: ${winner}<br>The stones at start of turn are: ${start}<br>
                The ${player} will take: ${move} stones<br>There remaining stones are: ${stones}`;

        })
        .catch(error => {
            console.error("Error fetching data: " + error);
        });
}