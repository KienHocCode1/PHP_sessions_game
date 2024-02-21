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

/**
 * Function to get random word from wordlist file, trim, and lowercase word
 *
 * @return string Random word
 */
function getRandomWord()
{
    $wordlist = file('wordlist.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    // array_rand returns random index of wordlist then assign that to $wordlist -> trim -> lowercase
    // without trim, there will be extra '-----'
    return strtolower(trim($wordlist[array_rand($wordlist)]));
}

/**
 * Function to initialize a new Hangman game by getting a random word, creating a secret with dashes,
 * and returning the initial game state as an associative array.
 *
 * @return array Initial game state, secret word, '-' equal to word's length, player's guesses, available letters, 
 *               strikes(incorrect guesses), and update status
 */
function initializeGame()
{
    // get random word
    $word = getRandomWord();
    // create '-' equal to $word length
    $secret = str_repeat('-', strlen($word));
    return [
        'word' => $word,
        'secret' => $secret,
        'guesses' => [],
        'alphabet' => range('a', 'z'),
        'strikes' => 0,
        'status' => 'new game started',
    ];
}

/**
 * Function to update the game state based on a guessed letter, including handling correct and incorrect guesses,
 * updating the secret word, and determining if the player has won or lost the game.
 *
 * @param array  $game   Current game state
 * @param string $letter Guessed letter
 *
 * @return array Updated game state
 */
function updateGame($game, $letter)
{
    // just to make sure input letter is in lowercase
    $letter = strtolower($letter);

    // Check if the letter has already been guessed, return current state, makes it seems nothing happen
    // if 'a' in guesses array and its true return $game
    if (in_array($letter, $game['guesses'], true)) {
        return $game;
    }

    // add guessed letter to guesses array
    // used [] before ['guesses] turns it to an array
    $game['guesses'][] = $letter;
    // sort them for displaying
    sort($game['guesses']);
    // remove the guessed $letter from the 'alphabet'
    $game['alphabet'] = array_diff($game['alphabet'], [$letter]);

    // Check if the guessed letter is in the word, if letter is in ['word] == true
    if (strpos($game['word'], $letter) !== false) {
        // loop over $game['word] initialized at line 42
        for ($i = 0; $i < strlen($game['word']); $i++) {
            // If the $letter match $game['word][$i] then update/display it to $game['secret'] on screen
            // in this scenario 'word' === 'secret'(100% same)
            if ($game['word'][$i] === $letter) {
                $game['secret'][$i] = $letter;
            }
        }

        // Check if the entire word matches the game and display the winner
        if ($game['secret'] === $game['word']) {
            $game['status'] = 'you have won the game!';
        } else {
            $game['status'] = 'you are playing a game now';
        }
    } else {
        // Increase 'strikes' if the guessed letter is not in the word
        $game['strikes']++;

        // game ends if guesses = 7
        if ($game['strikes'] >= 7) {
            $game['status'] = 'you have lost the game!';

            // Reveal the word when the player loses (strikes == 7)
            $game['secret'] = $game['word'];
        } else {
            $game['status'] = 'you are playing a game now';
        }
    }

    // return the updated game state
    return $game;
}

// start session
session_start();

// Check if the 'reset' button is clicked, then initialize a new game
// refer to js file line 56 'mode' and line 35 for $_SESSION
if (isset($_GET['mode']) && strtolower($_GET['mode']) === 'reset') {
    $_SESSION['hangman'] = initializeGame(); // see line 34
}

// Retrieve the current game state from the session or initialize a new game
$game = isset($_SESSION['hangman']) ? $_SESSION['hangman'] : initializeGame();

// Check if a letter has been selected, then updateGame() the game state
if (isset($_GET['letter'])) {
    $game = updateGame($game, $_GET['letter']);
    $_SESSION['hangman'] = $game;
}

header('Content-Type: application/json');
// json output
echo json_encode([
    // join array into string seperated by ''. 
    'guesses' => implode('', $game['guesses']),
    'alphabet' => implode('', $game['alphabet']),
    'secret' => $game['secret'],
    'strikes' => $game['strikes'],
    'status' => $game['status'],
]);
