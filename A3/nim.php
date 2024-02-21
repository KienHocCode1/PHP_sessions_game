<?php

/**
 * I, Trung Kien Bui, 000356049, certify that this material is my original work. No other person's
 * work has been used without acknowledgment and I have not made my work available to anyone else.
 */

/**
 * @author Trung Kien Bui <trung-kien.bui@mohawkcollege.ca>
 * @package COMP 10260 Assignment3
 * 
 * @version 202335.00
 */

// Starting a new session for the game
session_start();

/**
 * Function to check if an element exists in the session
 * 
 * @param string $elementId The ID of the element to check
 * @return bool Returns true if the element exists, false otherwise
 */
function elementExists($elementId)
{
    return isset($_SESSION[$elementId]);
}

/**
 * Function to make a move in the game
 * 
 * @param int $mode Game mode (0 for initialization, 1 for playing)
 * @param int $difficulty Computer difficulty level (0 for easy, 1 for hard)
 * @param int $stones Number of stones in the game
 * @param int|null $playerMove Number of stones player wants to remove in their move (null if computer's turn)
 * @return array Returns an array with move details (move, remaining stones, current player, winner)
 */
function makeMove($mode, $difficulty, $stones, $playerMove)
{
    // If mode == 1, start the game
    if ($mode == 1) {
        // If stones > 0 === no winner decided yet, run the game
        // player or computer makes $stones = 0 is the loser. 
        // current currentplayer initialize = 'computer' 
        if ($stones > 0) {
            // It's player's turn
            if ($_SESSION['currentPlayer'] === 'Player') {
                // playerMove set to null by default, != null, run player choice
                if ($playerMove !== null) {
                    // just making sure $playerMove is valid. remaining stones = 2 but player play 3 === invalid.
                    if ($playerMove < 1 || $playerMove > 3 || $playerMove > $stones) {
                        // refer to js file line 51. return an array
                        // if player's move is invalid, basically do nothing
                        return [
                            'move' => $playerMove,
                            'stones' => $stones >= 0 ? $stones : 0,
                            'player' => 'Player',
                            'winner' => 'undetermined',
                        ];
                    }
                    // if player move is valid. stones - player's choice(1/2/3) refer to line 133
                    // set currentPlayer to PLAYER then switch
                    $stones -= $playerMove;
                    $player = 'Player';
                }
                // Computer's turn
            } else {
                // easy mode scenario
                if ($difficulty == 0) {
                    // random 1-3 for computer's play. min(3, $stones) used to make sure computer doesnt take more stones than available,
                    // stones = 2 => min 3 and 2 is 2
                    $playerMove = rand(1, min(3, $stones));
                    // difficulty == 1. Hard mode
                } else {
                    $remainder = $stones % 4;
                    switch ($remainder) {
                        case 3:
                            $playerMove = 2;
                            break;
                        case 2:
                            $playerMove = 1;
                            break;
                        case 1:
                            $playerMove = rand(1, min(3, $stones));
                            break;
                        case 0:
                            $playerMove = 3;
                            break;
                    }
                }
                // set currentPlayer to COMPUTER then switch
                // -= stones based on computer's choice
                $stones -= $playerMove;
                $player = 'Computer';
            }
        }
    } else {
        // Game initialization
        $player = 'Computer';
        // prevent reset button adding extra stones
        $playerMove = 0;
    }

    // Switching the current player for the next turn
    // when the game is reset and started currentplayer = 'computer' then code runs to here first then switch currentplayer ='player' then
    // apply currentPlayer = 'Player' scenario
    $_SESSION['currentPlayer'] = $player === 'Player' ? 'Computer' : 'Player';

    // when stone = 0 set session variable currentPlayer to $winner. if stone > 0 $winner ='undetermined'
    // $stone = 0 => retrieve session variable currentPlayer then display
    $winner = ($stones <= 0) ? $_SESSION['currentPlayer'] : 'undetermined';

    // Set message if the game has just started, else set $winner = player or computer
    $winner = $stones == 20 ? 'game is started' : $winner;

    // Return move details, refer to js file line 51-54
    return [
        'move' => $playerMove,
        'stones' => $stones >= 0 ? $stones : 0,
        'player' => $player,
        'winner' => $winner,
    ];
}

// Get game parameters from the URL or use default values. RESET1/2/3 in order to demonstrate how RESET button work
// convert values to integer using intval()
// if mode is isset && = 1, start the game, else default to 0 (reset button state), see line 123(reset)
// destroy session variables (line 123) then set $stone to session variable = 20 (see line 118)
$mode = isset($_GET['mode']) ? intval($_GET['mode']) : 0;

// if difficulty = 0 set to easy mode, if diff =1 set to optimal/hard mode
$difficulty = isset($_GET['difficulty']) ? intval($_GET['difficulty']) : 0;

// if stones count doesn't exist set value to 20. Purpose: set stones to 20 by default. when click on reset button. RESET3
$stones = isset($_GET['count']) ? intval($_GET['count']) : 20;

// if player_move != 1, 2, 3 set to null. Retrieve value from const url from js file
$playerMove = isset($_GET['player_move']) ? intval($_GET['player_move']) : null;

// if mode === 0, unregister/clear/destroy session variables. RESET1
if ($mode === 0) {
    unset($_SESSION['stones']);
    unset($_SESSION['playerMove']);
    unset($_SESSION['currentPlayer']);
}

// If $stone doesnt exist && $mode = 0(default state) =>set stones session value to 20(line 118). else set  
// current stones (session variable) to $stones. RESET2
if (!elementExists('stones') && $mode === 0) {
    $_SESSION['stones'] = $stones;
} else {
    $stones = $_SESSION['stones'];
}

// if player_move doesn't exist set to null, else set current session variable (1/2/3) to $playerMove
// set to null by default RESET (line 126)
if (!elementExists('playerMove')) {
    $_SESSION['playerMove'] = $playerMove;
} else {
    $playerMove = $_SESSION['playerMove'];
}

// set Computer to currentPlayer when RESET button is clicked
if (!elementExists('currentPlayer')) {
    $_SESSION['currentPlayer'] = 'Computer';
}

// Make a move and get the response to display on screen
$response = makeMove($mode, $difficulty, $stones, $playerMove);

// Update the session variable with current stone
// see line 149, else part, then proceed to line 45
$_SESSION['stones'] = $response['stones'];

//reset player_move, prevent carryover from last move
$_SESSION['playerMove'] = null;

header('Content-Type: application/json');
echo json_encode($response);
