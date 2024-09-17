<?php
session_start();

// Initialize game state if not set
if (!isset($_SESSION['game'])) {
    $_SESSION['game'] = [
        'round' => 1,
        'player1_score' => 0,
        'player2_score' => 0,
        'draws' => 0,
        'game_over' => false,
        'player1_choice' => null,
        'player2_choice' => null,
        'current_player' => 1,
        'history' => [],
        'last_round_result' => '',
    ];
}

$choice_emojis = [
    'rock' => '🪨',
    'paper' => '📄',
    'scissors' => '✂️'
];

$win_conditions = [
    'rock' => 'scissors',
    'paper' => 'rock',
    'scissors' => 'paper'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$_SESSION['game']['game_over']) {
    $current_player = $_SESSION['game']['current_player'];
    $choice = $_POST['choice'];
    
    if ($current_player == 1) {
        $_SESSION['game']['player1_choice'] = $choice;
        $_SESSION['game']['current_player'] = 2;
    } else {
        $_SESSION['game']['player2_choice'] = $choice;
        $_SESSION['game']['current_player'] = 1;
        
        $player1_choice = $_SESSION['game']['player1_choice'];
        $player2_choice = $_SESSION['game']['player2_choice'];
        
        $player1_emoji = $choice_emojis[$player1_choice];
        $player2_emoji = $choice_emojis[$player2_choice];
        
        if ($player1_choice === $player2_choice) {
            $_SESSION['game']['draws']++;
            $result = "Draw";
        } elseif ($win_conditions[$player1_choice] === $player2_choice) {
            $_SESSION['game']['player1_score']++;
            $result = "Player 1 won";
        } else {
            $_SESSION['game']['player2_score']++;
            $result = "Player 2 won";
        }

        $_SESSION['game']['last_round_result'] = $result;
        $_SESSION['game']['history'][] = "Round {$_SESSION['game']['round']}: {$result} | {$player1_emoji}/{$player2_emoji}";
        
        if ($_SESSION['game']['player1_score'] == 4 || $_SESSION['game']['player2_score'] == 4 || $_SESSION['game']['round'] == 8) {
            $_SESSION['game']['game_over'] = true;
        } else {
            $_SESSION['game']['round']++;
        }

        $_SESSION['game']['player1_choice'] = $_SESSION['game']['player2_choice'] = null;
    }
}

if ($_SESSION['game']['game_over']) {
    if ($_SESSION['game']['player1_score'] > $_SESSION['game']['player2_score']) {
        $final_result = "Player 1 wins the game!";
    } elseif ($_SESSION['game']['player1_score'] < $_SESSION['game']['player2_score']) {
        $final_result = "Player 2 wins the game!";
    } else {
        $final_result = "The game is a tie!";
    }
    $_SESSION['game']['history'][] = $final_result;
}

if (isset($_POST['reset'])) {
    unset($_SESSION['game']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rock Paper Scissors - 2 Players</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
        }
        .game-wrapper {
            display: flex;
            justify-content: space-between;
            width: 90%;
            max-width: 1200px;
            gap: 20px;
        }
        .side-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 1rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 20%;
            height: 400px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .game-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 50%;
        }
        h1, h2, h3 {
            text-align: center;
            color: #4a4a4a;
        }
        .choices {
            display: flex;
            justify-content: space-around;
            margin-bottom: 2rem;
        }
        .choice {
            font-size: 3rem;
            background: none;
            border: none;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .choice:hover {
            transform: scale(1.2);
        }
        .result {
            text-align: center;
            font-weight: bold;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            color: #4a4a4a;
        }
        .score {
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            color: #4a4a4a;
        }
        .reset {
            display: block;
            margin: 0 auto;
            padding: 0.8rem 1.5rem;
            background-color: #764ba2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        .reset:hover {
            background-color: #667eea;
        }
        .history-item {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="game-wrapper">
        <div class="side-container">
            <h3>Last Round Result</h3>
            <div class="result">
                <?php echo $_SESSION['game']['last_round_result'] ? $_SESSION['game']['last_round_result'] : 'Game not started'; ?>
            </div>
        </div>
        <div class="game-container">
            <h1>Rock Paper Scissors</h1>
            <div class="score">
                Round: <?php echo $_SESSION['game']['round']; ?>/8<br>
                Player 1: <?php echo $_SESSION['game']['player1_score']; ?> | 
                Player 2: <?php echo $_SESSION['game']['player2_score']; ?> | 
                Draws: <?php echo $_SESSION['game']['draws']; ?>
            </div>
            <?php if (!$_SESSION['game']['game_over']): ?>
                <h2>Player <?php echo $_SESSION['game']['current_player']; ?>'s Turn</h2>
                <form method="post">
                    <div class="choices">
                        <button class="choice" name="choice" value="rock">🪨</button>
                        <button class="choice" name="choice" value="paper">📄</button>
                        <button class="choice" name="choice" value="scissors">✂️</button>
                    </div>
                </form>
                
            <?php else: ?>
                <div class="result"><?php echo $final_result; ?></div>
            <?php endif; ?>
            <form method="post">
                <button class="reset" name="reset">Reset Game</button>
            </form>
        </div>
        <div class="side-container">
            <h3>Game History</h3>
            <?php foreach ($_SESSION['game']['history'] as $item): ?>
                <div class="history-item"><?php echo $item; ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
