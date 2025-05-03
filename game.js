// Game of Life implementation
class GameOfLife {
	constructor() {
		this.width = 20;
		this.height = 20;
		this.grid = [];
		this.animateID = null;
		this.animateFPS = 5;
		this.animateCounter = 0;
		this.generation = 0;
		this.population = 0;
		this.presets = {
			block: [[0,0,0,0],[0,1,1,0],[0,1,1,0],[0,0,0,0]],
			boat: [[0,0,0,0,0],[0,1,1,0,0],[0,1,0,1,0],[0,0,1,0,0],[0,0,0,0,0]],
			beehive: [[0,0,0,0,0,0],[0,0,1,1,0,0],[0,1,0,0,1,0],[0,0,1,1,0,0],[0,0,0,0,0,0]],
			blinker: [[0,0,0,0,0],[0,0,1,0,0],[0,0,1,0,0],[0,0,1,0,0],[0,0,0,0,0]],
			beacon: [[0,0,0,0,0,0],[0,1,1,0,0,0],[0,1,1,0,0,0],[0,0,0,1,1,0],[0,0,0,1,1,0],[0,0,0,0,0,0]],
			glider: [[0,0,0,0,0],[0,0,1,0,0],[0,0,0,1,0],[0,1,1,1,0],[0,0,0,0,0]]
		};
	}

	init() {
		console.log("Initializing game...");
		// Create grid
		const gameContainer = document.getElementById('game-container');
		const table = document.createElement('table');
		table.className = 'conway-canvas';
		gameContainer.appendChild(table);

		// Initialize grid array and create cells
		for (let i = 0; i < this.height; i++) {
			this.grid.push([]);
			const row = document.createElement('tr');
			row.className = `row${i}`;
			table.appendChild(row);

			for (let j = 0; j < this.width; j++) {
				this.grid[i].push(0);
				const cell = document.createElement('td');
				cell.className = 'cell';
				cell.dataset.x = j;
				cell.dataset.y = i;
				row.appendChild(cell);

				// Add click event to toggle cell state
				cell.addEventListener('click', () => {
					console.log("Cell clicked");
					if (cell.classList.contains('conway-on')) {
						cell.classList.remove('conway-on');
						this.grid[i][j] = 0;
					} else {
						cell.classList.add('conway-on');
						this.grid[i][j] = 1;
					}
					this.updatePopulation();
				});
			}
		}

		// Connect UI controls
		document.getElementById('start').addEventListener('click', () => {
			console.log("Start button clicked");
			if (this.animateID === null) {
				this.animate();
			}
		});

		document.getElementById('stop').addEventListener('click', () => {
			console.log("Stop button clicked");
			if (this.animateID !== null) {
				cancelAnimationFrame(this.animateID);
				this.animateID = null;
			}
		});

		document.getElementById('step').addEventListener('click', () => {
			console.log("Step button clicked");
			this.update();
		});

		document.getElementById('fastForward').addEventListener('click', () => {
			console.log("Fast Forward button clicked");
			for (let i = 0; i < 23; i++) {
				this.update();
			}
		});

		document.getElementById('reset').addEventListener('click', () => {
			console.log("Reset button clicked");
			this.update('reset');
		});

		document.getElementById('preset').addEventListener('change', (e) => {
			console.log("Preset changed to:", e.target.value);
			this.update('preset', e.target.value);
		});

		document.getElementById('speed').addEventListener('change', (e) => {
			console.log("Speed changed to:", e.target.value);
			this.animateFPS = parseInt(e.target.value);
		});

		// Initialize with empty grid
		this.update('reset');
		console.log("Game initialization complete");
	}

	animate() {
		console.log("Animating...");
		this.animateID = requestAnimationFrame(() => this.animate());
		this.animateCounter++;
		if (this.animateCounter % this.animateFPS === 0) {
			this.animateCounter = 0;
			this.update();
		}
	}

	update(type = null, preset = null) {
		console.log("Updating game state:", type);
		if (type === 'reset') {
			for (let i = 0; i < this.height; i++) {
				for (let j = 0; j < this.width; j++) {
					this.grid[i][j] = 0;
					document.querySelector(`.cell[data-x="${j}"][data-y="${i}"]`).classList.remove('conway-on');
				}
			}
			this.generation = 0;
			this.population = 0;
		} else if (type === 'preset' && preset && preset !== '0') {
			const presetGrid = this.presets[preset];
			const startX = Math.floor((this.width - presetGrid[0].length) / 2);
			const startY = Math.floor((this.height - presetGrid.length) / 2);
			
			// Clear grid
			this.update('reset');
			
			// Apply preset
			for (let i = 0; i < presetGrid.length; i++) {
				for (let j = 0; j < presetGrid[i].length; j++) {
					const x = startX + j;
					const y = startY + i;
					if (x >= 0 && x < this.width && y >= 0 && y < this.height) {
						this.grid[y][x] = presetGrid[i][j];
						if (presetGrid[i][j] === 1) {
							document.querySelector(`.cell[data-x="${x}"][data-y="${y}"]`).classList.add('conway-on');
						}
					}
				}
			}
			this.updatePopulation();
		} else {
			// Calculate next generation
			const futureGrid = [];
			for (let i = 0; i < this.height; i++) {
				futureGrid.push([]);
				for (let j = 0; j < this.width; j++) {
					const neighbors = this.calcNeighbors(j, i);
					if (neighbors < 2) {
						futureGrid[i][j] = 0;
					} else if (this.grid[i][j] === 1 && (neighbors === 2 || neighbors === 3)) {
						futureGrid[i][j] = 1;
					} else if (this.grid[i][j] === 1 && neighbors > 3) {
						futureGrid[i][j] = 0;
					} else if (this.grid[i][j] === 0 && neighbors === 3) {
						futureGrid[i][j] = 1;
					} else {
						futureGrid[i][j] = 0;
					}
				}
			}
			this.grid = futureGrid;

			// Update display
			for (let i = 0; i < this.height; i++) {
				for (let j = 0; j < this.width; j++) {
					const cell = document.querySelector(`.cell[data-x="${j}"][data-y="${i}"]`);
					if (this.grid[i][j] === 1) {
						cell.classList.add('conway-on');
					} else {
						cell.classList.remove('conway-on');
					}
				}
			}

			this.generation++;
			this.updatePopulation();
		}
	}

	calcNeighbors(x, y) {
		let neighbors = 0;
		for (let i = -1; i <= 1; i++) {
			for (let j = -1; j <= 1; j++) {
				if (i === 0 && j === 0) continue;
				const nx = (x + j + this.width) % this.width;
				const ny = (y + i + this.height) % this.height;
				neighbors += this.grid[ny][nx];
			}
		}
		return neighbors;
	}

	updatePopulation() {
		this.population = 0;
		for (let i = 0; i < this.height; i++) {
			for (let j = 0; j < this.width; j++) {
				if (this.grid[i][j] === 1) {
					this.population++;
				}
			}
		}
	}
}

// Initialize game when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
	const game = new GameOfLife();
	game.init();
});
					