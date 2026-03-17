        let currentAudio = new Audio();
        let isPlayerReady = false;
        let isClearingMusic = false; 
        window.playerRequestId = 0; // Track fetches to prevent race conditions

        const isAuthenticated = window.AURORA.isAuthenticated;
        const currentUsername = window.AURORA.username;

        // Global Helper: Get Cookie Value
        function getCookie(name) {
            let cookieValue = null;
            if (document.cookie && document.cookie !== '') {
                const cookies = document.cookie.split(';');
                for (let i = 0; i < cookies.length; i++) {
                    const cookie = cookies[i].trim();
                    if (cookie.substring(0, name.length + 1) === (name + '=')) {
                        cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                        break;
                    }
                }
            }
            return cookieValue;
        }

        // Persistent Storage Helpers
        function getStorageKey(base) {
            if (!currentUsername) return null;
            return `mh_${currentUsername}_${base}`;
        }

        // Debug Tool
        window.debugPlayer = function() {
            if (!currentUsername) {
                console.log("%c[PlayerDebug] No user logged in.", "color: #ff4444; font-weight: bold;");
                return;
            }
            console.log(`%c[PlayerDebug] User: ${currentUsername}`, "color: #00C78A; font-weight: bold;");
            console.log("Queue:", JSON.parse(localStorage.getItem(getStorageKey('player_queue')) || "[]"));
            console.log("Last Song:", JSON.parse(localStorage.getItem(getStorageKey('last_song')) || "{}"));
            console.log("Index:", localStorage.getItem(getStorageKey('player_index')));
            console.log("Explicit Clear:", localStorage.getItem(getStorageKey('explicit_clear')));
        };

        // Navigation state helper
        window.updateBackBtnStatus = function() {
            const backBtn = document.getElementById('header-back-btn');
            if (!backBtn) return;
            const path = window.location.pathname;
            // Disable if at Homepage or recommended page (/)
            if (path === '/' || path === '') {
                backBtn.classList.add('disabled');
            } else {
                backBtn.classList.remove('disabled');
            }
        };

        // NEW: Safe Back Navigation to protect Audio Context
        window.handleHeaderBack = function(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            // 1. If we are in the "Playing" state (PDV active), close it first
            const pdv = document.getElementById('player-details-view');
            if (pdv && pdv.classList.contains('active')) {
                if (window.closePDV) {
                    window.closePDV(true);
                    return;
                }
            }

            // 2. Perform smooth back navigation
            if (window.history.length > 1) {
                window.history.back();
            } else {
                // Fallback to home via SPA if there's no history stack
                if (window.navigatePage) {
                    window.navigatePage('/', false);
                } else {
                    window.location.href = '/';
                }
            }
        };

        // --- Lyric Management Engine ---
        window.lyricManager = {
            lines: [],
            currentIndex: -1,
            container: null,
            
            reset() {
                this.lines = [];
                this.currentIndex = -1;
            },

            parse(lrcText) {
                this.reset();
                const lines = lrcText.split('\n');
                // Regex to match [00:00.00] or [00:00.000]
                const timeRegex = /\[(\d{2}):(\d{2})\.(\d{2,3})\]/;
                
                lines.forEach(line => {
                    const match = timeRegex.exec(line);
                    if (match) {
                        const minutes = parseInt(match[1]);
                        const seconds = parseInt(match[2]);
                        const msStr = match[3];
                        const ms = parseInt(msStr);
                        // Standardize to seconds
                        const time = minutes * 60 + seconds + (ms / (msStr.length === 2 ? 100 : 1000));
                        const text = line.replace(timeRegex, '').trim();
                        if (text) {
                            this.lines.push({ time, text });
                        }
                    }
                });
                this.lines.sort((a, b) => a.time - b.time);
            },

            render(container) {
                if (!container) return;
                this.container = container;
                if (this.lines.length === 0) {
                    container.innerHTML = '<p style="opacity: 0.5;">No lyrics found in file</p>';
                    return;
                }

                let html = '<div class="pdv-lyrics-container">';
                this.lines.forEach((line, index) => {
                    html += `<div class="lrc-line" data-index="${index}" onclick="lyricManager.seek(${line.time}, event)">${line.text}</div>`;
                });
                html += '</div>';
                container.innerHTML = html;
                
                // Immediately center the first line
                setTimeout(() => this.update(0), 50);
            },

            update(currentTime) {
                if (this.lines.length === 0 || !this.container) return;
                
                let index = -1;
                for (let i = 0; i < this.lines.length; i++) {
                    if (currentTime >= this.lines[i].time) {
                        index = i;
                    } else {
                        break;
                    }
                }
                
                if (index !== this.currentIndex) {
                    this.currentIndex = index;
                    const linesNodes = this.container.querySelectorAll('.lrc-line');
                    linesNodes.forEach(n => n.classList.remove('active'));
                    
                    const targetIndex = index === -1 ? 0 : index;
                    const activeNode = linesNodes[targetIndex];
                    
                    if (activeNode) {
                        if (index !== -1) activeNode.classList.add('active');
                        
                        const scrollBox = this.container.querySelector('.pdv-lyrics-container');
                        if (scrollBox) {
                            const boxHeight = scrollBox.clientHeight || 500; 
                            const lineCenter = activeNode.offsetTop + (activeNode.offsetHeight / 2);
                            // Set scroll so line is approx 30% from the top
                            scrollBox.scrollTop = lineCenter - Math.floor(boxHeight * 0.3);
                        }
                    }
                }
            },
            
            seek(time, event) {
                if (event) event.stopPropagation(); // Prevent PDV close if triggered by bar
                if (typeof currentAudio !== 'undefined') {
                    currentAudio.currentTime = time;
                    if (currentAudio.paused) currentAudio.play();
                }
            }
        };

        // --- NEW: SPA-lite Navigation Logic ---
        function syncSidebarWithURL(url) {
            const urlObj = new URL(url, window.location.origin);
            const targetPath = urlObj.pathname;
            
            // Extract playlist ID if we're on a playlist page
            let currentPlaylistId = null;
            const pathParts = targetPath.split('/').filter(p => p !== '');
            if (pathParts[0] === 'playlist' && pathParts[1]) {
                currentPlaylistId = pathParts[1];
            }

            document.querySelectorAll('.sidebar-pill').forEach(pill => {
                const href = pill.getAttribute('href');
                const onclick = pill.getAttribute('onclick') || '';
                const dataId = pill.getAttribute('data-playlist-id');
                const favoritedPlaylists = typeof window.favoritedPlaylists === 'undefined' ? new Set() : window.favoritedPlaylists;
                const isStarred = favoritedPlaylists.has(String(dataId)); // Use dataId for playlistId
                
                // Reset active first
                pill.classList.remove('active');

                // Case 1: Match by exact href path
                if (href && href !== '#' && !href.startsWith('javascript:')) {
                    const pillPath = new URL(href, window.location.origin).pathname;
                    if (pillPath === targetPath) {
                        pill.classList.add('active');
                    }
                } 
                // Case 2: Match by data-playlist-id (for dynamically loaded playlists)
                else if (dataId && currentPlaylistId && dataId === currentPlaylistId) {
                    pill.classList.add('active');
                }
                // Case 3: Home/Featured fallback
                else if (onclick.includes('featured')) {
                    if (targetPath === '/' || targetPath === '') pill.classList.add('active');
                }
            });
        }

        function navigatePage(url, pushState = true, skipAnimation = false) {
            const dynamicContent = document.getElementById('dynamic-content');
            const pdv = document.getElementById('player-details-view');
            
            if (!dynamicContent) {
                window.location.href = url;
                return;
            }

            // Automatically skip animation for settings or edit paths
            if (url.includes('/settings/') || url.includes('/edit/')) {
                skipAnimation = true;
            }

            // Logic to skip PDV in "return journey":
            // If we are currently in PDV (URL is /playing/), we should REPLACE the state 
            // instead of pushing, so that going back skips the player screen.
            if (pushState && (window.location.pathname === '/playing/' || (pdv && pdv.classList.contains('active')))) {
                pushState = 'replace'; 
            }

            // Also ensure PDV is closed if we are navigating away
            if (pdv && pdv.classList.contains('active')) {
                pdv.classList.remove('active');
                document.body.classList.remove('pdv-active');
            }

            if (!skipAnimation) {
                dynamicContent.style.opacity = '0.4';
                dynamicContent.style.transition = 'opacity 0.2s ease';
            }

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newDynamic = doc.querySelector('#dynamic-content');
                    
                    if (newDynamic) {
                        // 1. Sync Styles & Scripts (Cleanup old ones first)
                        document.querySelectorAll('.injected-asset').forEach(el => el.remove());

                        // Build sets of already-present assets to avoid duplicating base CSS
                        const existingHrefs = new Set(
                            Array.from(document.head.querySelectorAll('link[rel="stylesheet"]'))
                                .map(l => l.getAttribute('href'))
                        );
                        const existingStyleLengths = new Set(
                            Array.from(document.head.querySelectorAll('style'))
                                .map(s => s.textContent.length)
                        );

                        const newStyles = doc.head.querySelectorAll('style, link[rel="stylesheet"]');
                        newStyles.forEach(el => {
                            if (el.tagName === 'LINK') {
                                if (existingHrefs.has(el.getAttribute('href'))) return;
                            } else if (el.tagName === 'STYLE') {
                                if (existingStyleLengths.has(el.textContent.length)) return;
                            }
                            const clone = el.cloneNode(true);
                            clone.classList.add('injected-asset');
                            document.head.appendChild(clone);
                        });

                        // 2. Pre-highlight the active song BEFORE injecting into DOM
                        togglePlayPauseIcons(!currentAudio.paused, newDynamic);

                        // 3. Physically update the content
                        dynamicContent.innerHTML = newDynamic.innerHTML;
                        document.title = doc.title;
                        
                        // 4. RESET UI STATE INSTANTLY
                        // We snap opacity back to 1 immediately so content is visible right away.
                        dynamicContent.style.transition = 'none'; 
                        dynamicContent.style.opacity = '1';
                        
                        // Force-disable entrance animation only if explicitly requested
                        if (skipAnimation) {
                            const entranceElement = dynamicContent.querySelector('.page-entrance');
                            if (entranceElement) {
                                entranceElement.style.animation = 'none';
                                entranceElement.style.opacity = '1';
                                entranceElement.style.transform = 'none';
                            }
                        }

                        // 5. Manual Script Execution (since innerHTML doesn't execute scripts)
                        const scripts = newDynamic.querySelectorAll('script');
                        scripts.forEach(oldScript => {
                            const newScript = document.createElement('script');
                            Array.from(oldScript.attributes).forEach(attr => {
                                newScript.setAttribute(attr.name, attr.value);
                            });
                            newScript.textContent = oldScript.textContent;
                            newScript.classList.add('injected-asset');
                            document.body.appendChild(newScript);
                        });

                        // 6. Final cleanup of animation locks
                        // Since we just did a normal navigation, ensure the lock is off immediately
                        document.documentElement.classList.remove('no-entrance-anim');
                        
                        if (pushState === 'replace') {
                            history.replaceState({ url: url }, doc.title, url);
                        } else if (pushState) {
                            history.pushState({ url: url }, doc.title, url);
                        }
                        syncSidebarWithURL(url);
                        
                        // Scroll: restore position when closing PDV, otherwise go to top
                        const mainArea = document.querySelector('.main-scrollable-area');
                        if (mainArea) {
                            if (skipAnimation && window._pdvScrollPos !== undefined) {
                                mainArea.scrollTop = window._pdvScrollPos;
                                window._pdvScrollPos = undefined;
                            } else {
                                mainArea.scrollTop = 0;
                            }
                        }

                        // Re-trigger page features
                        // Re-trigger page features with a safe delay for SPA layout stabilization
                        setTimeout(() => {
                            if (window.initPageFeatures) window.initPageFeatures();
                        }, 200);
                        
                        // Update back button state
                        updateBackBtnStatus();
                    } else {
                        console.warn("Target #dynamic-content not found, falling back to full reload");
                        window.location.href = url;
                    }
                })
                .catch(err => {
                    console.error('Failed SPA load:', err);
                    window.location.href = url;
                });
        }

        window.onpopstate = function(event) {
            const pdv = document.getElementById('player-details-view');
            const isPlayingPath = window.location.pathname === '/playing/';

            if (isPlayingPath) {
                // If we hit /playing/ in the return journey, skip it
                history.back();
                return;
            } else {
                // If we navigated AWAY from the playing view, close it
                let wasPDV = document.documentElement.classList.contains('no-entrance-anim');
                
                if (pdv && pdv.classList.contains('active')) {
                    pdv.classList.remove('active');
                    document.body.classList.remove('pdv-active');
                    wasPDV = true;
                    document.documentElement.classList.add('no-entrance-anim');
                }

                const finishRefresh = () => {
                    // Only remove the lock after navigation and content injection is likely finished
                    setTimeout(() => {
                        document.documentElement.classList.remove('no-entrance-anim');
                    }, 600); 
                };

                if (event.state && event.state.url) {
                    navigatePage(event.state.url, false, wasPDV);
                    if (wasPDV) finishRefresh();
                } else if (event.state && event.state.type === 'playlist') {
                    renderPlaylistDetailView(event.state.id, false, false, wasPDV);
                    updateSidebarHighlight('playlist', { id: event.state.id });
                    if (wasPDV) finishRefresh();
                } else {
                    const currentUrl = window.location.pathname + window.location.search;
                    navigatePage(currentUrl, false, wasPDV);
                    if (wasPDV) finishRefresh();
                }
                updateBackBtnStatus();
            }
        };

        // Reset opacity on back/forward navigation (fixes the dimming issue)
        window.addEventListener('pageshow', function (event) {
            const dynamicContent = document.getElementById('dynamic-content');
            if (dynamicContent) {
                dynamicContent.style.opacity = '1';
                dynamicContent.style.transition = 'none'; // Force instant reset
                setTimeout(() => { dynamicContent.style.transition = 'opacity 0.3s ease'; }, 10);
            }
        });

        // Intercept sidebar links and internal links
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            
            // Ignore if link is missing, has a literal onclick attribute, or lacks an href attribute
            if (!link || link.getAttribute('onclick') || !link.hasAttribute('href')) return;

            // Block clicks on unknown metadata placeholders
            if (link.classList.contains('metadata-unknown') || (link.href && link.href.includes('javascript:void(0)'))) {
                e.preventDefault();
                return;
            }

            // Must have href and not be a special internal schema
            if (!link.href || link.href.startsWith('blob:') || link.hasAttribute('download') || link.hasAttribute('data-skip-spa')) return;
            
            let url;
            try {
                url = new URL(link.href);
            } catch (err) {
                return; 
            }

            const isInternal = url.origin === window.location.origin;
            const rawHref = link.getAttribute('href') || '';
            const isSpecial = url.pathname.includes('/admin/') || url.pathname.includes('/logout/') || rawHref.startsWith('#');

            if (isInternal && !isSpecial) {
                e.preventDefault();
                // Close PDV before navigating if it's open
                if (window.closePDV) window.closePDV(false);
                navigatePage(link.href);
            }
        });

        // --- QUEUE MANAGEMENT SYSTEM ---
        try {
            const savedQueue = localStorage.getItem(getStorageKey('player_queue'));
            const savedIndex = localStorage.getItem(getStorageKey('player_index'));
            window.playerQueue = savedQueue ? JSON.parse(savedQueue) : [];
            window.queueIndex = savedIndex ? parseInt(savedIndex) : -1;
        } catch (e) {
            console.error('Error loading queue from storage', e);
            window.playerQueue = [];
            window.queueIndex = -1;
        }

        function saveQueueState() {
            const queueKey = getStorageKey('player_queue');
            const indexKey = getStorageKey('player_index');
            if (queueKey) localStorage.setItem(queueKey, JSON.stringify(window.playerQueue));
            if (indexKey) localStorage.setItem(indexKey, window.queueIndex);
        }

        function updateQueueUI(skipSave = false) {
            const list = document.getElementById('queue-songs-list');
            const count = document.getElementById('queue-count');
            if (!list) return;

            if (window.playerQueue.length === 0) {
                list.innerHTML = '<div style="padding: 40px; text-align: center; color: rgba(255,255,255,0.4);">Queue is empty</div>';
                if (count) count.textContent = '0';
                if (!skipSave) saveQueueState();
                return;
            }

            if (count) count.textContent = window.playerQueue.length;

            list.innerHTML = window.playerQueue.map((song, index) => {
                const isActive = (index === window.queueIndex);
                const isPlaying = isActive && !currentAudio.paused;
                
                return `
                <div class="queue-item ${isActive ? 'active' : ''}" data-index="${index}" onclick="if(!event.target.closest('a') && !event.target.closest('.row-action-btn') && !event.target.closest('.song-row-heart')) playFromQueue(${index})">
                    <div class="queue-cover-wrapper">
                        <img src="${song.cover}" class="queue-item-cover" onerror="this.src='/media/covers/default_cover.jpg'">
                        <div class="queue-play-overlay">
                            ${isPlaying ? `
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#00C78A" viewBox="0 0 16 16">
                                    <path d="M5.5 3.5A1.5 1.5 0 0 1 7 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5zm5 0A1.5 1.5 0 0 1 12 5v6a1.5 1.5 0 0 1-3 0V5a1.5 1.5 0 0 1 1.5-1.5z"/>
                                </svg>
                            ` : `
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="#00C78A" viewBox="0 0 16 16">
                                    <path d="m11.596 8.697-6.363 3.692c-.54.313-1.233-.066-1.233-.697V4.308c0-.63.692-1.01 1.233-.696l6.363 3.692a.802.802 0 0 1 0 1.393z"/>
                                </svg>
                            `}
                        </div>
                    </div>
                    <div class="queue-item-info">
                        <div class="queue-item-name">${song.title}</div>
                        ${(() => {
                            const isUnknownArtist = !song.artist || song.artist === '未知' || (typeof song.artist === 'string' && song.artist.toLowerCase().includes('unknown'));
                            if (isUnknownArtist) {
                                return `<div class="queue-item-artist metadata-unknown" style="display: block;">Unknown Artist</div>`;
                            } else {
                                const artists = song.artist.split('|').map(a => a.trim()).filter(a => a);
                                if (artists.length > 1) {
                                    const links = artists.map(a => `<a href="/artist/${encodeURIComponent(a)}/" class="artist-link" style="color: inherit; text-decoration: none; transition: color 0.3s ease;" onmouseover="this.style.color='#00C78A'" onmouseout="this.style.color='inherit'">${a}</a>`).join(' / ');
                                    return `<div class="queue-item-artist" style="display: block; width: fit-content; color: rgba(255,255,255,0.4);">${links}</div>`;
                                } else {
                                    return `<a href="/artist/${encodeURIComponent(song.artist)}/" class="queue-item-artist" style="color: inherit; text-decoration: none; display: block; width: fit-content; transition: color 0.3s ease;" onmouseover="this.style.color='#00C78A'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">${song.artist}</a>`;
                                }
                            }
                        })()}
                    </div>
                    <div class="queue-item-right">
                        <div class="queue-item-duration">${song.duration || '--:--'}</div>
                        <div class="queue-item-actions">
                            <!-- Like Button (Sync with Library) -->
                            <div class="song-row-heart ${song.isLiked ? 'is-liked' : ''}" 
                                 data-song-id="${song.id}" 
                                 onclick="toggleLike('${song.id}', this, true)"
                                 style="transform: translateY(1px);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#ff4444" viewBox="0 0 16 16" class="heart-filled">
                                    <path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z"/>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="heart-empty">
                                    <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z"/>
                                </svg>
                            </div>

                            <!-- Add to Playlist Button (Sync with Library) -->
                            <div class="row-action-btn" title="Add to Playlist" 
                                 onclick="window.triggerAddToPlaylist('${song.id}')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 12H3"></path><path d="M16 6H3"></path><path d="M16 18H3"></path><path d="M18 9v6"></path><path d="M21 12h-6"></path>
                                </svg>
                            </div>

                            <!-- Remove from Queue -->
                            <div class="row-action-btn" title="Remove" onclick="window.removeFromQueue(${index})">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            }).join('');
            if (!skipSave) saveQueueState();
        }

        // Initial UI Update
        document.addEventListener('DOMContentLoaded', () => {
            updateQueueUI();
        });

        window.addToQueue = function(song, playNow = false) {
            // Check if already in queue
            const existingIndex = window.playerQueue.findIndex(s => s.id == song.id);
            if (existingIndex === -1) {
                window.playerQueue.push(song);
                if (playNow) {
                    window.queueIndex = window.playerQueue.length - 1;
                }
            } else {
                // If it already exists, sync metadata like heart status
                if (typeof song.isLiked !== 'undefined') {
                    window.playerQueue[existingIndex].isLiked = song.isLiked;
                }
                if (playNow) {
                    window.queueIndex = existingIndex;
                }
            }
            updateQueueUI();
        };

        window.playFromQueue = function(index) {
            const song = window.playerQueue[index];
            if (!song) return;
            
            if (index === window.queueIndex) {
                // Same song, toggle play pause
                if (currentAudio.paused) currentAudio.play();
                else currentAudio.pause();
                return;
            }

            window.queueIndex = index;
            loadAndPlay(song.url, song.title, song.artist, song.cover, song.id, true, song.album);
            updateQueueUI();
        };

        window.removeFromQueue = function(index) {
            const wasPlaying = !currentAudio.paused;
            const removedIndex = index;
            
            window.playerQueue.splice(index, 1);
            
            if (window.playerQueue.length === 0) {
                if (window.clearAuroraPlayer) window.clearAuroraPlayer();
            } else {
                if (removedIndex === window.queueIndex) {
                    // Choose next song
                    if (window.queueIndex >= window.playerQueue.length) {
                        // Loop back to top
                        window.queueIndex = 0;
                    }
                    // Play or load the new song at this index
                    const song = window.playerQueue[window.queueIndex];
                    loadAndPlay(song.url, song.title, song.artist, song.cover, song.id, wasPlaying, song.album);
                } else if (removedIndex < window.queueIndex) {
                    // Shift index down
                    window.queueIndex--;
                }
            }
            updateQueueUI();
        };

        window.playNext = function(auto = true) {
            if (window.playerQueue.length === 0) return;
            
            const state = document.querySelector('.player-loop-btn')?.getAttribute('data-state') || 'repeat-all';
            
            if (state === 'repeat-one') {
                currentAudio.currentTime = 0;
                currentAudio.play();
                return;
            }

            let nextIndex;
            if (state === 'shuffle') {
                if (window.playerQueue.length <= 1) {
                    nextIndex = 0;
                } else {
                    // Pick a random index that isn't the current one
                    do {
                        nextIndex = Math.floor(Math.random() * window.playerQueue.length);
                    } while (nextIndex === window.queueIndex);
                }
            } else {
                nextIndex = window.queueIndex + 1;
                if (nextIndex >= window.playerQueue.length) {
                    nextIndex = 0; // Loop back
                }
            }

            if (nextIndex === window.queueIndex) {
                currentAudio.currentTime = 0;
                currentAudio.play().then(() => {
                    if (typeof togglePlayPauseIcons === 'function') togglePlayPauseIcons(true);
                });
            } else {
                window.playFromQueue(nextIndex);
            }
        };

        window.playPrev = function() {
            if (window.playerQueue.length === 0) return;
            
            const state = document.querySelector('.player-loop-btn')?.getAttribute('data-state') || 'repeat-all';
            if (state === 'repeat-one') {
                currentAudio.currentTime = 0;
                currentAudio.play();
                return;
            }

            let prevIndex = window.queueIndex - 1;
            if (prevIndex < 0) {
                prevIndex = window.playerQueue.length - 1; // Loop back to bottom
            }

            if (prevIndex === window.queueIndex) {
                currentAudio.currentTime = 0;
                currentAudio.play().then(() => {
                    if (typeof togglePlayPauseIcons === 'function') togglePlayPauseIcons(true);
                });
            } else {
                window.playFromQueue(prevIndex);
            }
        };

        window.clearAuroraPlayer = function() {
            window.isClearingMusic = true;
            window.playerRequestId++; // Invalidate all pending fetches
            
            // 1. CLEAR MEMORY STATE
            window.playerQueue = [];
            window.queueIndex = -1;
            window.currentSongId = null;
            
            // 2. CLEAR STORAGE
            const qK = getStorageKey('player_queue');
            const iK = getStorageKey('player_index');
            const lK = getStorageKey('last_song');
            const eK = getStorageKey('explicit_clear');
            if (qK) localStorage.removeItem(qK);
            if (iK) localStorage.removeItem(iK);
            if (lK) localStorage.removeItem(lK);
            if (eK) localStorage.setItem(eK, 'true'); // Prevent recovery on refresh
            
            // 3. STOP AUDIO ENGINE
            if (currentAudio) {
                currentAudio.pause();
                currentAudio.src = '';
                currentAudio.removeAttribute('src'); 
                currentAudio.load(); 
            }
            
            // 4. RESET GLOABL PLAYER BAR
            updateQueueUI();
            if (typeof updatePlayerUI === 'function') {
                updatePlayerUI('No Track Selected', 'Unknown Artist', null, 'Unknown Album');
            }
            if (typeof togglePlayPauseIcons === 'function') {
                togglePlayPauseIcons(false);
            }
            
            
            // Disable hover triggers for Like and Comment
            const likeContainerEl = document.getElementById('player-like-container');
            const commentBtnEl = document.getElementById('player-comment-btn');
            if (likeContainerEl) likeContainerEl.classList.add('no-song');
            if (commentBtnEl) commentBtnEl.classList.add('no-song');

            // Reset count numbers to visually hide them
            const likeCountEl = document.getElementById('player-like-count');
            const commentCountEl = document.getElementById('player-comment-count');
            if (likeCountEl) { 
                likeCountEl.style.opacity = '0';
                likeCountEl.style.transform = 'scale(0.8)'; 
            }
            if (commentCountEl) { 
                commentCountEl.style.opacity = '0';
                commentCountEl.style.transform = 'scale(0.8)'; 
            }
            
            // 5. AUTO-CLOSE PDV
            if (window.closePDV) {
                window.closePDV(true);
            } else {
                const pdvOverlay = document.getElementById('player-details-view');
                if (pdvOverlay && pdvOverlay.classList.contains('active')) {
                    pdvOverlay.classList.remove('active');
                    document.body.classList.remove('pdv-active');
                }
            }

            setTimeout(() => { window.isClearingMusic = false; }, 500);
        };

        window.logoutPlayerReset = function() {
            // 1. Stop Audio
            if (currentAudio) {
                currentAudio.pause();
                currentAudio.src = '';
            }
            // 2. Reset In-Memory state
            window.playerQueue = [];
            window.queueIndex = -1;
            window.currentSongId = null;
            // 3. Reset UI
            if (typeof updatePlayerUI === 'function') {
                updatePlayerUI('No Track Selected', 'Unknown Artist', null, 'Unknown Album');
            }
            if (typeof togglePlayPauseIcons === 'function') {
                togglePlayPauseIcons(false);
            }
            // Disable hover triggers for Like and Comment
            const likeContainerEl = document.getElementById('player-like-container');
            const commentBtnEl = document.getElementById('player-comment-btn');
            if (likeContainerEl) likeContainerEl.classList.add('no-song');
            if (commentBtnEl) commentBtnEl.classList.add('no-song');

            const likeCountEl = document.getElementById('player-like-count');
            const commentCountEl = document.getElementById('player-comment-count');
            if (likeCountEl) { likeCountEl.style.opacity = '0'; likeCountEl.style.transform = 'scale(0.8)'; }
            if (commentCountEl) { commentCountEl.style.opacity = '0'; commentCountEl.style.transform = 'scale(0.8)'; }

            updateQueueUI(true); // SKIP SAVE! Do not overwrite storage during logout
            console.log("[Player] Logout reset complete (storage preserved)");
        };

        window.clearAuroraQueue = function() {
            window.clearAuroraPlayer();
        };

        // --- QUEUE UI HELPERS ---
        window.openQueueMenu = function(e, index) {
            e.preventDefault();
            e.stopPropagation();
            
            // Remove existing menus
            const existingMenu = document.querySelector('.queue-context-menu');
            if (existingMenu) existingMenu.remove();

            const song = window.playerQueue[index];
            const menu = document.createElement('div');
            menu.className = 'queue-context-menu active';
            menu.innerHTML = `
                <div class="queue-menu-item" onclick="removeFromQueue(${index})">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                        <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                    </svg>
                    <span>Remove from Queue</span>
                </div>
                <div class="queue-menu-item" onclick="showHubToast('Next up: ${song.title}')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445z"/>
                    </svg>
                    <span>Play Next</span>
                </div>
                ${window.AURORA.isAuthenticated ? `
                <div class="queue-menu-item" onclick="window.triggerAddToPlaylist('${song.id}')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                    </svg>
                    <span>Add to Playlist</span>
                </div>` : ''}
            `;

            const item = e.target.closest('.queue-item');
            item.appendChild(menu);

            // Close menu when clicking elsewhere
            const closeMenu = (ev) => {
                if (!menu.contains(ev.target)) {
                    menu.remove();
                    document.removeEventListener('click', closeMenu);
                }
            };
            setTimeout(() => document.addEventListener('click', closeMenu), 10);
        };

        // Player Details View (PDV) Open/Close Logic
        document.addEventListener('DOMContentLoaded', function() {
            const globalPlayerBar = document.getElementById('global-player-bar');
            const pdv = document.getElementById('player-details-view');
            const closeBtn = document.getElementById('pdv-close-btn');

            // --- URL ROUTING & PERSISTENCE ---
            window.togglePDVCommentMode = function(force) {
                const pdv = document.getElementById('player-details-view');
                if (!pdv) return;
                
                if (typeof force !== 'undefined') {
                    if (force) pdv.classList.add('comment-mode');
                    else pdv.classList.remove('comment-mode');
                } else {
                    pdv.classList.toggle('comment-mode');
                }
                
                const isCommentMode = pdv.classList.contains('comment-mode');
                if (isCommentMode) {
                    sessionStorage.setItem('pdv_comment_mode', 'true');
                    if (window.refreshPDVComments && window.currentSongId) {
                        window.refreshPDVComments(window.currentSongId);
                    }
                } else {
                    sessionStorage.removeItem('pdv_comment_mode');
                }
            };

            window.openPDV = function(push = true) {
                // If no song is selected or queue is empty, block access
                if (!window.playerQueue || window.playerQueue.length === 0 || !window.currentSongId) {
                    return;
                }
                if (!isAuthenticated) return;
                if (!pdv || pdv.classList.contains('active')) return;
                pdv.classList.add('active');
                document.body.classList.add('pdv-active');
                if (push) {
                    history.pushState({ type: 'pdv' }, 'Now Playing', '/playing/');
                }
                
                // --- Persist Comment Mode ---
                if (sessionStorage.getItem('pdv_comment_mode') === 'true') {
                    const commentView = document.getElementById('pdv-comments-view');
                    if (commentView) commentView.style.transition = 'none';
                    
                    pdv.classList.add('comment-mode');
                    if (window.refreshPDVComments && window.currentSongId) {
                        window.refreshPDVComments(window.currentSongId);
                    }
                    
                    if (commentView) {
                        setTimeout(() => { commentView.style.transition = ''; }, 100);
                    }
                }

                // Pre-update PDV components (Mini Header etc)
                if (window.updatePDVComponents) window.updatePDVComponents();
            };

            window.updatePDVComponents = function() {
                const miniCover = document.getElementById('pdv-mini-cover');
                const miniTitle = document.getElementById('pdv-mini-title');
                const miniArtist = document.getElementById('pdv-mini-artist');
                
                // Sync with main player UI
                const currentTitle = document.querySelector('.player-title').textContent;
                const currentArtist = document.querySelector('.player-artist').textContent;
                const currentCover = document.querySelector('.player-cover img') ? document.querySelector('.player-cover img').src : '';
                
                if(miniTitle) miniTitle.textContent = currentTitle;
                if(miniArtist) miniArtist.textContent = currentArtist;
                if(miniCover) miniCover.src = currentCover;
            };




            window.closePDV = function(push = true) {
                if (!pdv || !pdv.classList.contains('active')) return;

                // Save scroll position BEFORE triggering history.back() so navigatePage can restore it
                const mainArea = document.querySelector('.main-scrollable-area');
                window._pdvScrollPos = mainArea ? mainArea.scrollTop : 0;

                // Critical: Activate animation lock BEFORE triggering history changes
                document.documentElement.classList.add('no-entrance-anim');

                pdv.classList.remove('active');
                pdv.classList.remove('comment-mode');
                // Persistence is now maintained even after closing, as per user request
                document.body.classList.remove('pdv-active');
                if (push && window.location.pathname === '/playing/') {
                    // If the user manually closes, go back in history
                    history.back();
                }
            };

            // ---------------------------------

            if (globalPlayerBar && pdv) {
                globalPlayerBar.addEventListener('click', function(e) {
                    // Ignore clicks on interactive child elements
                    if (e.target.closest('button, .player-like-btn, .volume-container, .progress-wrapper, .player-menu-container, a, svg')) {
                        return;
                    }
                    if (pdv.classList.contains('active')) {
                        closePDV();
                    } else {
                        openPDV();
                    }
                });

                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        closePDV();
                    });
                }

                // PDV Tab Switching Logic
                const pdvTabs = document.querySelectorAll('.pdv-tab');
                pdvTabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        pdvTabs.forEach(t => t.classList.remove('active'));
                        this.classList.add('active');
                        
                        const tabName = this.textContent.trim().toLowerCase();
                        const panes = document.querySelectorAll('.pdv-tab-pane');
                        panes.forEach(p => {
                            p.style.display = 'none';
                            p.classList.remove('active-pane');
                        });
                        
                        if (tabName === 'lyrics') {
                            const pane = document.getElementById('pdv-tab-lyrics');
                            if (pane) {
                                pane.style.display = 'block';
                                pane.classList.add('active-pane');
                            }
                        } else if (tabName === 'encyclopedia') {
                            const pane = document.getElementById('pdv-tab-encyclopedia');
                            if (pane) {
                                pane.style.display = 'block';
                                pane.classList.add('active-pane');
                            }
                        } else if (tabName === 'similar') {
                            const pane = document.getElementById('pdv-tab-similar');
                            if (pane) {
                                pane.style.display = 'block';
                                pane.classList.add('active-pane');
                            }
                        }
                    });
                });
            }
        });

        // --- END SPA-lite ---

        window.syncPlayerCommentCount = function(songId) {
            if (!songId || window.currentSongId != songId) return;
            fetch(`/api/song-details/${songId}/`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const playerCommentCount = document.getElementById('player-comment-count');
                        if (playerCommentCount) {
                            const comments = data.comments_count || 0;
                            playerCommentCount.textContent = comments.toLocaleString();
                        }
                    }
                })
                .catch(err => console.error("Failed to sync comment count", err));
        };

        function updatePlayerUI(title, artist, cover, album) {
            const playerTitle = document.querySelector('.player-title');
            const playerArtist = document.querySelector('.player-artist');
            const pdvTitle = document.getElementById('pdv-title');
            const pdvArtist = document.getElementById('pdv-artist');
            const pdvAlbum = document.getElementById('pdv-album');
            const pdvCover = document.getElementById('pdv-cover');

            if (playerTitle) playerTitle.textContent = title || 'No Track Selected';
            if (pdvTitle) pdvTitle.textContent = title || 'No Track Selected';

            const masterPlayBtn = document.getElementById('master-play-btn');
            if (masterPlayBtn) {
                if (!title || title === 'No Track Selected') {
                    masterPlayBtn.classList.add('disabled');
                } else {
                    masterPlayBtn.classList.remove('disabled');
                }
            }

            const processMetadata = (val, type, displayEls, linkIds) => {
                const isUnknown = !val || val === '未知' || (typeof val === 'string' && val.toLowerCase().includes('unknown'));
                const els = Array.isArray(displayEls) ? displayEls : [displayEls];
                
                const isArtist = type === 'artist' && !isUnknown;
                let artists = [];
                if (isArtist && typeof val === 'string') {
                    artists = val.split('|').map(a => a.strip ? a.strip() : a.trim()).filter(a => a);
                }
                const isMultiArtist = artists.length > 1;

                els.forEach(el => {
                    if (el) {
                        if (isArtist && !isUnknown) {
                            // Render individual artist links
                            const sep = '<span class="artist-separator">/</span>';
                            el.innerHTML = artists.map(a => `<a href="/artist/${encodeURIComponent(a)}/" class="artist-link">${a}</a>`).join(sep);
                        } else if (isUnknown) {
                            el.innerHTML = `<span class="metadata-unknown">${type === 'artist' ? 'Unknown Artist' : 'Unknown Album'}</span>`;
                        } else {
                            el.textContent = val;
                        }
                    }
                });
                
                linkIds.forEach(id => {
                    const link = document.getElementById(id);
                    if (link) {
                        if (isUnknown) {
                            link.classList.add('metadata-unknown');
                            link.removeAttribute('href');
                            link.style.cursor = '';
                            link.style.pointerEvents = 'auto';
                        } else {
                            link.classList.remove('metadata-unknown');
                            // If it's multi-artist, we don't want the parent wrapper to act as a link anymore
                            if (type === 'artist' && artists.length > 1) {
                                link.removeAttribute('href');
                                link.style.cursor = 'default';
                                link.style.pointerEvents = 'none'; // Let children handle clicks
                            } else {
                                const hrefVal = artists.length > 0 ? artists[0] : val;
                                link.href = `/${type}/${encodeURIComponent(hrefVal)}/`;
                                link.style.cursor = 'pointer';
                                link.style.pointerEvents = 'auto';
                            }
                            link.style.color = '';
                            link.removeAttribute('onmouseover');
                            link.removeAttribute('onmouseout');
                        }
                    }
                });
            };

            // Toggle PDV access styling
            const playerBar = document.getElementById('global-player-bar');
            if (playerBar) {
                const isLocked = !window.playerQueue || window.playerQueue.length === 0 || !window.currentSongId;
                if (isLocked) {
                    playerBar.classList.add('pdv-locked');
                } else {
                    playerBar.classList.remove('pdv-locked');
                }
            }

            processMetadata(artist, 'artist', [playerArtist, pdvArtist], ['player-artist-link', 'pdv-artist-link', 'player-menu-artist-link']);
            processMetadata(album, 'album', pdvAlbum, ['pdv-album-link', 'player-menu-album-link']);

            const coverPlaceholder = document.querySelector('.player-cover');
            if (cover) {
                const imgHtml = `<img src="${cover}" alt="Cover" style="width: 100%; height: 100%; object-fit: cover;">`;
                if (coverPlaceholder) coverPlaceholder.innerHTML = imgHtml;
                if (pdvCover) pdvCover.src = cover;
            } else {
                // Reset to default icon
                if (coverPlaceholder) {
                    coverPlaceholder.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="rgba(255,255,255,0.3)" viewBox="0 0 16 16">
                            <path d="M9 13c0 1.105-1.12 2-2.5 2S4 14.105 4 13s1.12-2 2.5-2 2.5.895 2.5 2z" />
                            <path fill-rule="evenodd" d="M9 3v10H8V3h1z" />
                            <path d="M8 2.82a1 1 0 0 1 .804-.98l3-.6A1 1 0 0 1 13 2.22V4l-5 1V2.82z" />
                        </svg>`;
                }
                if (pdvCover) pdvCover.src = '/media/covers/default_cover.jpg'; // Or keep it empty? User said cover still shows, so let's reset it.
            }
        }

        function togglePlayPauseIcons(isPlaying, container = document) {
            const playBtn = document.getElementById('master-play-btn');
            const disc = document.querySelector('.player-cover img');
            if (playBtn) {
                const iconPlay = playBtn.querySelector('.icon-play');
                const iconPause = playBtn.querySelector('.icon-pause');

                if (isPlaying) {
                    if (iconPlay) iconPlay.style.display = 'none';
                    if (iconPause) iconPause.style.display = 'block';
                    if (disc) {
                        disc.classList.add('rotating-disc');
                        disc.classList.remove('paused');
                    }
                    // Sync PDV Vinyl
                    const pdvVinyl = document.getElementById('pdv-vinyl');
                    const pdvStylus = document.getElementById('pdv-stylus');
                    if (pdvVinyl) {
                        pdvVinyl.classList.add('rotating');
                        pdvVinyl.classList.remove('paused');
                    }
                    if (pdvStylus) pdvStylus.classList.add('playing');
                } else {
                    if (iconPlay) iconPlay.style.display = 'block';
                    if (iconPause) iconPause.style.display = 'none';
                    if (disc) disc.classList.add('paused');
                    
                    // Sync PDV Vinyl
                    const pdvVinyl = document.getElementById('pdv-vinyl');
                    const pdvStylus = document.getElementById('pdv-stylus');
                    if (pdvVinyl) pdvVinyl.classList.add('paused');
                    if (pdvStylus) pdvStylus.classList.remove('playing');
                }
            }

            // Sync other UI elements (cards/rows) in the provided container
            const allCards = container.querySelectorAll('.glass-card');
            const allRows = container.querySelectorAll('.song-row');
            const allTvItems = container.querySelectorAll('.topviews-item');

            allCards.forEach(card => {
                const songUrl = card.getAttribute('data-song-url');
                const isCurrent = (songUrl && currentAudio.src.endsWith(songUrl)) || (card.getAttribute('data-song-id') == window.currentSongId);

                card.classList.toggle('is-active', isCurrent);
                card.classList.toggle('is-playing', isCurrent && isPlaying);

                // Toggle icons (fallback to JS for certainty)
                const cPlay = card.querySelector('.play-icon');
                const cPause = card.querySelector('.pause-icon');
                if (cPlay && cPause) {
                    if (isCurrent && isPlaying) {
                        cPlay.style.setProperty('display', 'none', 'important');
                        cPause.style.setProperty('display', 'block', 'important');
                    } else {
                        cPlay.style.setProperty('display', 'block', 'important');
                        cPause.style.setProperty('display', 'none', 'important');
                    }
                }
            });

            allTvItems.forEach(item => {
                const isCurrent = item.getAttribute('data-song-id') == window.currentSongId;
                item.classList.toggle('is-active', isCurrent);
                item.classList.toggle('is-playing', isCurrent && isPlaying);
                const tPlay = item.querySelector('.tv-play-overlay .play-icon');
                const tPause = item.querySelector('.tv-play-overlay .pause-icon');
                if (tPlay && tPause) {
                    tPlay.style.display = (isCurrent && isPlaying) ? 'none' : 'block';
                    tPause.style.display = (isCurrent && isPlaying) ? 'block' : 'none';
                }
            });

            const allRecentItems = container.querySelectorAll('.recent-item');
            allRecentItems.forEach(item => {
                const isCurrent = item.getAttribute('data-song-id') == window.currentSongId;
                item.classList.toggle('is-active', isCurrent);
                item.classList.toggle('is-playing', isCurrent && isPlaying);
                const rPlay = item.querySelector('.recent-play-overlay .play-icon');
                const rPause = item.querySelector('.recent-play-overlay .pause-icon');
                if (rPlay && rPause) {
                    rPlay.style.display = (isCurrent && isPlaying) ? 'none' : 'block';
                    rPause.style.display = (isCurrent && isPlaying) ? 'block' : 'none';
                }
            });

            allRows.forEach(row => {
                const isCurrent = (row.getAttribute('data-song-id') == window.currentSongId);
                row.classList.toggle('is-active', isCurrent);
                row.classList.toggle('is-playing', isCurrent && isPlaying);
                
                const rPlay = row.querySelector('.icon-play, .play-icon');
                const rPause = row.querySelector('.icon-pause, .pause-icon');
                if (rPlay && rPause) {
                    if (isCurrent && isPlaying) {
                        rPlay.style.display = 'none';
                        rPause.style.display = 'block';
                    } else {
                        rPlay.style.display = 'block';
                        rPause.style.display = 'none';
                    }
                }
            });
        }

        // Helper to sync player UI whenever content changes
        function syncPlayerUI() {
            if (typeof togglePlayPauseIcons === 'function') {
                togglePlayPauseIcons(!currentAudio.paused);
            }
        }

        function savePlayerState(url, title, artist, cover, songId, album) {
            const state = { 
                url, title, artist, cover, songId, album,
                wasPlaying: !currentAudio.paused,
                timestamp: Date.now() 
            };
            const lastStateKey = getStorageKey('last_song');
            if (lastStateKey) localStorage.setItem(lastStateKey, JSON.stringify(state));
        }

        function loadAndPlay(url, title, artist, cover, songId = null, autoPlay = true, album = '未知', forceReset = false) {
            // Standardize both URLs to full absolute paths for accurate comparison
            const targetUrl = new URL(url, window.location.origin).href;
            const currentUrl = currentAudio.src ? new URL(currentAudio.src, window.location.origin).href : '';
            const isSameSong = (currentUrl === targetUrl);

            // Update Global State
            if (songId) {
                const isNewId = (window.currentSongId != songId);
                window.currentSongId = songId;

                // Enable hover on like and comment buttons now that a song is loaded
                const likeContainerEl = document.getElementById('player-like-container');
                const commentBtnEl = document.getElementById('player-comment-btn');
                if (likeContainerEl) likeContainerEl.classList.remove('no-song');
                if (commentBtnEl) commentBtnEl.classList.remove('no-song');

                if (isNewId) {
                    // Increment View Count
                    const postData = new FormData();
                    postData.append('song_id', songId);
                    
                    fetch('/api/increment-song-view/', {
                        method: 'POST',
                        headers: { 'X-CSRFToken': getCookie('csrftoken') },
                        body: postData
                    });

                    // Record to Play History
                    if (isAuthenticated) {
                        fetch('/api/record-recent-play/', {
                            method: 'POST',
                            headers: { 'X-CSRFToken': getCookie('csrftoken') },
                            body: postData
                        });
                    }

                    const playerHearts = [
                        document.getElementById('player-like-btn'),
                        document.getElementById('pdv-song-like-btn')
                    ].filter(Boolean);

                    playerHearts.forEach(btn => {
                        btn.setAttribute('data-song-id', songId);
                        btn.classList.remove('is-liked');
                    });

                    if (isAuthenticated) {
                        fetch(`/api/check-favorite/?song_id=${songId}`)
                            .then(r => r.json())
                            .then(data => {
                                playerHearts.forEach(btn => {
                                    if (data.is_liked) {
                                        btn.classList.add('is-liked');
                                        btn.setAttribute('data-song-liked', 'true');
                                    } else {
                                        btn.classList.remove('is-liked');
                                        btn.setAttribute('data-song-liked', 'false');
                                    }
                                });
                            });
                    }

                    // Fetch Song Details for PDV (Lyrics, Encyclopedia)
                    const thisRequestId = ++window.playerRequestId;
                    fetch(`/api/song-details/${songId}/`)
                        .then(r => r.json())
                        .then(data => {
                            if (thisRequestId !== window.playerRequestId) return; // Stale fetch
                            if (data.success) {
                                // 1. Update Metadata (using the central UI system to handle "Unknown" logic)
                                updatePlayerUI(title, data.artist || artist, cover, data.album || album);

                                // 2. Update Encyclopedia Data
                                const encyGenre = document.getElementById('ency-genre');
                                const encyDate = document.getElementById('ency-date');
                                const encyIntro = document.getElementById('ency-intro');
                                const encyDuration = document.getElementById('ency-duration');
                                const encyViews = document.getElementById('ency-views');

                                if (encyGenre) encyGenre.textContent = data.song_type || 'Unknown';
                                if (encyDate) encyDate.textContent = data.release_date || '-';
                                if (encyIntro) encyIntro.textContent = data.introduction || 'No introduction available.';
                                if (encyViews) encyViews.textContent = (data.views || 0).toLocaleString();
                                
                                // NEW: Update Player Like Count Badge
                                const playerLikeCount = document.getElementById('player-like-count');
                                if (playerLikeCount) {
                                    const likes = data.likes_count || 0;
                                    playerLikeCount.textContent = likes.toLocaleString();
                                    playerLikeCount.style.opacity = '1';
                                    playerLikeCount.style.transform = 'scale(1)';
                                }
                                
                                // NEW: Update Comment Count Badge
                                const playerCommentCount = document.getElementById('player-comment-count');
                                if (playerCommentCount) {
                                    const comments = data.comments_count || 0;
                                    playerCommentCount.textContent = comments.toLocaleString();
                                    playerCommentCount.style.opacity = '1';
                                    playerCommentCount.style.transform = 'scale(1)';
                                }
                                
                                // 4. If PDV is active, sync its metadata components
                                const pdvOverlay = document.getElementById('player-details-view');
                                if (pdvOverlay && pdvOverlay.classList.contains('active')) {
                                    if (window.updatePDVComponents) window.updatePDVComponents();
                                    // If in comment mode, refresh comments synchronously
                                    if (pdvOverlay.classList.contains('comment-mode') && window.refreshPDVComments) {
                                        window.refreshPDVComments(songId);
                                    }
                                }
                                if (encyDuration && typeof currentAudio !== 'undefined') {
                                    const updateDur = () => {
                                        const total = currentAudio.duration;
                                        if (total) {
                                            const m = Math.floor(total / 60);
                                            const s = Math.floor(total % 60);
                                            encyDuration.textContent = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                                        }
                                    };
                                    if (currentAudio.readyState >= 1) updateDur();
                                    else currentAudio.addEventListener('loadedmetadata', updateDur, { once: true });
                                }

                                // 3. Handle Lyrics
                                const lyricsPlaceholder = document.querySelector('.pdv-lyrics-placeholder');
                                if (lyricsPlaceholder) {
                                    const lrcData = data.lyrics || '';
                                    if (lrcData.includes('1145141919810')) {
                                        lyricsPlaceholder.innerHTML = '<p style="margin-bottom: 10px; font-size: 1.2em; opacity: 0.8;">Pure Music, Please Enjoy</p>';
                                        window.lyricManager.reset();
                                    } else if (lrcData.startsWith('/media/') || lrcData.toLowerCase().endsWith('.lrc')) {
                                        lyricsPlaceholder.innerHTML = '<p style="margin-bottom: 10px; opacity: 0.5;">Loading lyrics...</p>';
                                        fetch(lrcData)
                                            .then(r => r.text())
                                            .then(text => {
                                                if (thisRequestId !== window.playerRequestId) return; // Stale fetch
                                                window.lyricManager.parse(text);
                                                window.lyricManager.render(lyricsPlaceholder);
                                            })
                                            .catch(() => {
                                                lyricsPlaceholder.innerHTML = '<p style="margin-bottom: 10px;">Failed to load lyric file</p>';
                                            });
                                    } else {
                                        lyricsPlaceholder.innerHTML = '<p style="margin-bottom: 10px;">No lyrics available</p>';
                                        window.lyricManager.reset();
                                    }
                                }
                            }
                        })
                        .catch(err => console.error("Failed to fetch song details", err));
                }
            }

            if (!isSameSong) {
                // Scenario A: Loading a completely new track
                currentAudio.src = url;
                updatePlayerUI(title, artist, cover, album);
                
                // --- AUTO ADD TO QUEUE ---
                // Try to detect liked status from existing UI if available
                const initialLiked = !!document.querySelector(`.song-row-heart[data-song-id="${songId}"].is-liked`);
                
                addToQueue({
                    url, title, artist, cover, id: songId, album,
                    duration: '00:00', // Will update later if possible
                    isLiked: initialLiked
                }, true);
                // -------------------------

                savePlayerState(url, title, artist, cover, songId, album);
                const eK = getStorageKey('explicit_clear');
                if (eK) localStorage.setItem(eK, 'false'); // Valid play happened, reset clear flag
                
                const disc = document.querySelector('.player-cover img');
                if (disc) disc.classList.remove('rotating-disc', 'paused');
                
                const pdvVinyl = document.getElementById('pdv-vinyl');
                const pdvStylus = document.getElementById('pdv-stylus');
                if (pdvVinyl) pdvVinyl.classList.remove('rotating', 'paused');
                if (pdvStylus) pdvStylus.classList.remove('playing');

                if (autoPlay) {
                    currentAudio.play().then(() => togglePlayPauseIcons(true))
                                     .catch(e => console.error("Autoplay blocked:", e));
                } else {
                    togglePlayPauseIcons(false);
                }
            } else {
                // Scenario B: Clicking the same track that's already loaded
                if (forceReset) {
                    currentAudio.currentTime = 0;
                    if (autoPlay && currentAudio.paused) {
                        currentAudio.play().then(() => togglePlayPauseIcons(true));
                    } else if (!autoPlay && !currentAudio.paused) {
                        currentAudio.pause();
                        togglePlayPauseIcons(false);
                    }
                } else {
                    if (currentAudio.paused) {
                        currentAudio.play().then(() => togglePlayPauseIcons(true));
                    } else {
                        currentAudio.pause();
                        togglePlayPauseIcons(false);
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Initial Page Load Check for Playlists
            const pathParts = window.location.pathname.split('/').filter(p => p !== '');
            if (pathParts[0] === 'playlist' && pathParts[1]) {
                appNavigateTo('playlist', {id: pathParts[1]}, false);
            }

            // --- Search Selection/Highlighting Logic ---
            // (Handled via initPageFeatures at the end of this block)

            // --- Player Recovery Logic ---
            const lastStateKey = getStorageKey('last_song');
            const lastState = lastStateKey ? localStorage.getItem(lastStateKey) : null;
            
            // Distinguish between a Refresh (same session) and a New Login/Session
            const isRefresh = sessionStorage.getItem('aurora_refreshed') === 'true';
            sessionStorage.setItem('aurora_refreshed', 'true');

            // CRITICAL: If no lastState, and it's a new session, DON'T load backend song if not logged in
            const explicitClearKey = getStorageKey('explicit_clear');
            const explicitClear = explicitClearKey ? (localStorage.getItem(explicitClearKey) === 'true') : false;
            
            if (!currentAudio.src || currentAudio.src === window.location.origin + '/') {
                if (lastState) {
                    const s = JSON.parse(lastState);
                    // On recovery, we want to at least load the UI even if not auto-playing
                    const shouldPlay = isRefresh && s.wasPlaying;
                    console.log("[PlayerPersist] Recovering last song:", s.title);
                    loadAndPlay(s.url, s.title, s.artist, s.cover, s.songId, shouldPlay, s.album);
                } else {
                    updatePlayerUI('No Track Selected', 'Unknown Artist', null, 'Unknown Album');
                }
            } else {
                syncPlayerUI();
            }

            // --- PDV Recovery Check ---
            // Must happen AFTER player recovery logic so openPDV criteria are met
            if (window.location.pathname === '/playing/') {
                const pdv = document.getElementById('player-details-view');
                const commentView = document.getElementById('pdv-comments-view');
                if (pdv && window.openPDV) {
                    // Disable all transitions for instant snap on refresh
                    pdv.style.transition = 'none';
                    if (commentView) commentView.style.transition = 'none';
                    
                    window.openPDV(false);
                    
                    setTimeout(() => { 
                        pdv.style.transition = ''; 
                        if (commentView) commentView.style.transition = '';
                    }, 100);
                }
            }

            // Sync playback state to localStorage
            currentAudio.addEventListener('play', () => {
                if (window.isClearingMusic) return;
                const lastKey = getStorageKey('last_song');
                if (!lastKey) return;
                const s = JSON.parse(localStorage.getItem(lastKey) || '{}');
                if (s.url) {
                    s.wasPlaying = true;
                    localStorage.setItem(lastKey, JSON.stringify(s));
                }
            });
            currentAudio.addEventListener('pause', () => {
                if (window.isClearingMusic) return;
                const lastKey = getStorageKey('last_song');
                if (!lastKey) return;
                const s = JSON.parse(localStorage.getItem(lastKey) || '{}');
                if (s.url) {
                    s.wasPlaying = false;
                    localStorage.setItem(lastKey, JSON.stringify(s));
                }
            });

            // Volume Control Logic
            const volumeSlider = document.getElementById('volume-slider');
            const volumeVal = document.getElementById('volume-val');
            const muteBtn = document.getElementById('mute-btn');
            const volIconHigh = document.getElementById('vol-icon-high');
            const volIconMid = document.getElementById('vol-icon-mid');
            const volIconLow = document.getElementById('vol-icon-low');
            const volIconMute = document.getElementById('vol-icon-mute');
            let lastVolume = 0.8;

            function updateVolumeUI(vol) {
                currentAudio.volume = vol;
                if (volumeSlider) volumeSlider.value = vol;
                if (volumeVal) volumeVal.textContent = Math.round(vol * 100) + '%';

                // Hide all first
                if (volIconHigh) volIconHigh.style.display = 'none';
                if (volIconMid) volIconMid.style.display = 'none';
                if (volIconLow) volIconLow.style.display = 'none';
                if (volIconMute) volIconMute.style.display = 'none';

                if (vol == 0) {
                    if (volIconMute) volIconMute.style.display = 'block';
                } else if (vol <= 0.3) {
                    if (volIconLow) volIconLow.style.display = 'block';
                } else if (vol <= 0.7) {
                    if (volIconMid) volIconMid.style.display = 'block';
                } else {
                    if (volIconHigh) volIconHigh.style.display = 'block';
                }
            }

            if (volumeSlider) {
                // Recover Volume (Global, not per user)
                const savedVol = localStorage.getItem('mh_player_volume');
                if (savedVol !== null) {
                    lastVolume = parseFloat(savedVol);
                    updateVolumeUI(lastVolume);
                } else {
                    updateVolumeUI(volumeSlider.value);
                }

                volumeSlider.addEventListener('input', function () {
                    updateVolumeUI(this.value);
                    if (this.value > 0) {
                        lastVolume = this.value;
                        localStorage.setItem('mh_player_volume', lastVolume);
                    }
                });
            }

            if (muteBtn) {
                muteBtn.addEventListener('click', function () {
                    if (currentAudio.volume > 0) {
                        lastVolume = currentAudio.volume;
                        updateVolumeUI(0);
                    } else {
                        updateVolumeUI(lastVolume || 0.8);
                    }
                });
            }

            // Global Play/Pause Button
            const masterPlayBtn = document.getElementById('master-play-btn');
            const progContainer = document.getElementById('progress-container');
            const progBar = document.getElementById('progress-bar');
            const timeDisplay = document.getElementById('player-time');

            function formatTime(sec) {
                if (!sec) return '00:00';
                let m = Math.floor(sec / 60);
                let s = Math.floor(sec % 60);
                return (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
            }

            // Progress Bar & Dragging Logic
            let isDragging = false;

            function updateProgressUI(pos) {
                const duration = currentAudio.duration;
                const percent = pos * 100;

                // 1. Update width directly (ensure no CSS transition lag)
                progBar.style.width = percent + '%';

                if (duration && isFinite(duration)) {
                    timeDisplay.textContent = formatTime(pos * duration) + ' / ' + formatTime(duration);

                    // 2. Precise boundary checking for time capsule
                    const wrapperWidth = progContainer.offsetWidth;
                    // Use a fallback width if not rendered yet
                    const capsuleWidth = timeDisplay.offsetWidth || 85;
                    const leftPx = pos * wrapperWidth;

                    const halfCapsule = capsuleWidth / 2;
                    const padding = 10;

                    let offset = 0;
                    if (leftPx < halfCapsule + padding) {
                        offset = (halfCapsule + padding) - leftPx;
                    } else if (leftPx > wrapperWidth - halfCapsule - padding) {
                        offset = (wrapperWidth - halfCapsule - padding) - leftPx;
                    }

                    // Apply offset to keep it within view
                    timeDisplay.style.transform = `translateX(50%) translateX(${offset}px)`;
                }
            }

            currentAudio.addEventListener('timeupdate', () => {
                const currentTime = currentAudio.currentTime;
                if (!isDragging && currentAudio.duration) {
                    const pos = currentTime / currentAudio.duration;
                    updateProgressUI(pos);
                }
                // Sync Lyrics
                if (window.lyricManager && typeof window.lyricManager.update === 'function') {
                    window.lyricManager.update(currentTime);
                }
            });

            const handleSeek = (e) => {
                const rect = progContainer.getBoundingClientRect();
                let x = e.clientX - rect.left;
                let pos = x / rect.width;
                pos = Math.max(0, Math.min(1, pos));

                // Sync UI immediately in the same event loop
                updateProgressUI(pos);
                return pos;
            };

            progContainer.addEventListener('mousedown', function (e) {
                if (!currentAudio.duration || !isFinite(currentAudio.duration)) return;
                isDragging = true;
                progContainer.classList.add('is-dragging');
                document.body.classList.add('is-dragging-progress');

                handleSeek(e); // Update UI immediately
                timeDisplay.style.display = 'block'; // Force visible during drag
            });

            window.addEventListener('mousemove', (e) => {
                if (isDragging) {
                    handleSeek(e);
                }
            });

            window.addEventListener('mouseup', (e) => {
                if (isDragging) {
                    isDragging = false;
                    progContainer.classList.remove('is-dragging');
                    document.body.classList.remove('is-dragging-progress');
                    const pos = handleSeek(e);
                    currentAudio.currentTime = pos * currentAudio.duration;
                    timeDisplay.style.display = ''; // Revert to CSS hover
                }
            });

            // Diagnostic event listeners
            currentAudio.addEventListener('error', () => {
                console.error("[Player] Audio Error State:", currentAudio.error);
            });
            currentAudio.addEventListener('stalled', () => {
                console.warn("[Player] Playback stalled (waiting for data)");
            });
            if (masterPlayBtn) {
                masterPlayBtn.addEventListener('click', function () {
                    if (!currentAudio.src || currentAudio.src === window.location.origin + '/') return;
                    if (currentAudio.paused) {
                        currentAudio.play();
                    } else {
                        currentAudio.pause();
                    }
                });
            }

            // Centralized Event Listeners for Audio State
            currentAudio.addEventListener('play', () => {
                togglePlayPauseIcons(true);
                updateQueueUI(); // Sync queue item play/pause icon
            });
            currentAudio.addEventListener('pause', () => {
                togglePlayPauseIcons(false);
                updateQueueUI();
            });
            currentAudio.addEventListener('playing', () => togglePlayPauseIcons(true));

            // Card Play Buttons removed (redundant with onclick)

            // Audio Events
            currentAudio.addEventListener('ended', () => {
                playNext(true);
            });
            
            // Duration updates for queue
            currentAudio.addEventListener('loadedmetadata', () => {
                if (window.queueIndex !== -1 && window.playerQueue[window.queueIndex]) {
                    const duration = currentAudio.duration;
                    const m = Math.floor(duration / 60);
                    const s = Math.floor(duration % 60);
                    window.playerQueue[window.queueIndex].duration = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                    updateQueueUI();
                }
            });

            // Fail-safe UI sync (every second)
            setInterval(syncPlayerUI, 1000);
        });

        document.addEventListener('DOMContentLoaded', function () {
            // More Options Dropdown
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.player-more-btn');
                const menu = document.querySelector('.player-glass-menu');

                if (btn) {
                    e.preventDefault();
                    menu.classList.toggle('show');
                } else if (menu && !e.target.closest('.player-glass-menu')) {
                    menu.classList.remove('show');
                }
            });

            // Loop/Shuffle Cycle Logic (Repeat All -> Repeat One -> Shuffle -> Repeat All)
            const loopBtn = document.querySelector('.player-loop-btn');
            if (loopBtn) {
                loopBtn.addEventListener('click', function () {
                    const currentState = this.getAttribute('data-state');
                    const iconAll = this.querySelector('.icon-repeat-all');
                    const iconOne = this.querySelector('.icon-repeat-one');
                    const iconShuffle = this.querySelector('.icon-shuffle');

                    iconAll.style.display = 'none';
                    iconOne.style.display = 'none';
                    iconShuffle.style.display = 'none';

                    if (currentState === 'repeat-all') {
                        this.setAttribute('data-state', 'repeat-one');
                        this.setAttribute('title', 'Repeat One');
                        iconOne.style.display = 'block';
                    } else if (currentState === 'repeat-one') {
                        this.setAttribute('data-state', 'shuffle');
                        this.setAttribute('title', 'Shuffle');
                        iconShuffle.style.display = 'block';
                    } else {
                        // back to repeat-all
                        this.setAttribute('data-state', 'repeat-all');
                        this.setAttribute('title', 'Repeat All');
                        iconAll.style.display = 'block';
                    }
                });
            }
        });

        function applySearchHighlighting() {
            // ONLY run this on the library/search result pages as requested
            const path = window.location.pathname;
            if (!path.includes('/library/') && !path.includes('/search/') && path !== '/library' && path !== '/search') {
                return;
            }

            const urlParams = new URLSearchParams(window.location.search);
            const query = urlParams.get('q');
            if (query && query.length > 0) {
                const targetSelectors = [
                    '.song-title-text',
                    '.hover-marquee-content',
                    '.catalog-card h5',
                    '.song-index-col .index-num',
                    '.card-text h5',
                    '.shelf-title'
                ];
                
                const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const regex = new RegExp(`(${escapedQuery})`, 'gi');

                targetSelectors.forEach(selector => {
                    const elements = document.querySelectorAll(selector);
                    elements.forEach(el => {
                        // Skip if it contains children (we only want text nodes if possible, or handle carefully)
                        if (el.children.length === 0 || (el.children.length === 1 && el.children[0].tagName === 'A')) {
                            const originalHTML = el.innerHTML;
                            // Avoid double-wrapping if already highlighted
                            if (originalHTML.includes('search-highlight')) return;
                            
                            if (regex.test(originalHTML)) {
                                el.innerHTML = originalHTML.replace(regex, '<span class="search-highlight">$1</span>');
                            }
                        } else if (el.classList.contains('hover-marquee-content')) {
                            // Specialized handling for marquee titles/artists
                            const links = el.querySelectorAll('a, span');
                            links.forEach(link => {
                                const text = link.innerHTML;
                                if (text.includes('search-highlight')) return;
                                if (regex.test(text)) {
                                    link.innerHTML = text.replace(regex, '<span class="search-highlight">$1</span>');
                                }
                            });
                        }
                    });
                });
            }
        }

        function updateMarquees() {
            // Helper for robust width measurement
            function getTrueWidth(target, container) {
                if (!target) return 0;
                const ghost = target.cloneNode(true);
                ghost.style.cssText = 'position:absolute !important; top:-9999px !important; left:-9999px !important; width:auto !important; max-width:none !important; min-width:0 !important; display:inline-block !important; white-space:nowrap !important; visibility:hidden !important;';
                container.appendChild(ghost);
                const width = ghost.getBoundingClientRect().width;
                container.removeChild(ghost);
                return width;
            }

            // 1. Infinite Marquee (Song Titles)
            document.querySelectorAll('.song-title-wrapper').forEach(wrapper => {
                const marquee = wrapper.querySelector('.song-title-marquee');
                const link = wrapper.querySelector('.song-title-link');
                if (!marquee || !link) return;

                // ALWAYS RESET on navigation trips
                wrapper.classList.remove('is-overflowing', 'is-marquee-init');
                const nodes = Array.from(marquee.childNodes);
                nodes.forEach(n => { if (n !== link) marquee.removeChild(n); });

                const sw = getTrueWidth(link, marquee);
                const cw = wrapper.clientWidth;
                if (cw === 0) return; // Skip hidden elements

                if (sw > cw + 1) {
                    wrapper.classList.add('is-overflowing', 'is-marquee-init');
                    const clone = link.cloneNode(true);
                    marquee.appendChild(document.createTextNode('\u00A0\u00A0\u00A0\u00A0\u00A0'));
                    marquee.appendChild(clone);
                }
            });

            // 2. Hover-Triggered Marquee for Album/Artist
            document.querySelectorAll('.hover-marquee-wrapper').forEach(wrapper => {
                const content = wrapper.querySelector('.hover-marquee-content');
                if (!content) return;
                
                // 1. Restore clean state
                if (!content.dataset.originalHtml) {
                    content.dataset.originalHtml = content.innerHTML;
                } else {
                    content.innerHTML = content.dataset.originalHtml;
                }
                wrapper.classList.remove('is-overflowing', 'is-marquee-init');

                const cw = wrapper.clientWidth;
                if (cw === 0) return; // Skip hidden elements

                // 2. FORCE-MEASURE Total Width
                const ghost = content.cloneNode(true);
                ghost.style.cssText = 'position:absolute !important; top:-9999px !important; left:-9999px !important; width:auto !important; max-width:none !important; min-width:0 !important; display:inline-block !important; white-space:nowrap !important; visibility:hidden !important;';
                ghost.querySelectorAll('a').forEach(a => a.style.width = 'auto');
                document.body.appendChild(ghost);
                const sw = ghost.offsetWidth;
                document.body.removeChild(ghost);
                
                if (sw > cw + 1.5) {
                    wrapper.classList.add('is-overflowing', 'is-marquee-init');
                    
                    // Add spacer and clone the whole original block
                    const spacer = '<span class="js-marquee-unit js-marquee-spacer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
                    const clone = content.innerHTML;
                    content.innerHTML += spacer + clone;
                    // Tag clones for CSS control
                    Array.from(content.children).slice(content.children.length / 2).forEach(child => {
                         child.classList.add('js-marquee-unit', 'js-marquee-clone');
                    });
                }
            });
        }

        function initPageFeatures() {
            updateMarquees();
            applySearchHighlighting();
            // Clear stale transform:translateY(0) left by animation fill-mode:forwards.
            // Without this, .page-entrance becomes a position:fixed containing block,
            // causing fixed-positioned glass-menus to be offset from the viewport.
            document.querySelectorAll('.page-entrance').forEach(function(el) {
                el.addEventListener('animationend', function() {
                    this.style.opacity = '1';
                    this.style.animation = 'none';
                }, { once: true });
            });
            // Restore scroll position saved before login form submit
            var savedScroll = sessionStorage.getItem('restore_scroll');
            if (savedScroll !== null) {
                sessionStorage.removeItem('restore_scroll');
                var mainArea = document.querySelector('.main-scrollable-area');
                if (mainArea) mainArea.scrollTop = parseInt(savedScroll, 10);
            }
        }

        // Shared helper: restore a body-hoisted glass-menu back to its original parent
        function restoreMenu(m) {
            m.classList.remove('show');
            if (m._origParent !== undefined) {
                if (document.contains(m._origParent)) {
                    delete m._origParent._hoistedMenu;
                    m._origParent.appendChild(m);
                } else {
                    m.remove(); // original card was destroyed (e.g. SPA nav)
                }
                delete m._origParent;
            }
            m.style.cssText = '';
        }
        function closeAllMenus() {
            document.querySelectorAll('.glass-menu.show').forEach(restoreMenu);
            document.querySelectorAll('.glass-card.menu-active, .topviews-item.menu-active').forEach(c => c.classList.remove('menu-active'));
        }
        window.closeAllMenus = closeAllMenus;

        document.addEventListener('DOMContentLoaded', function () {
            // Dropdown click logic for glass cards ... (rest of the logic)
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.more-btn');
                if (btn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const card = btn.closest('.glass-card, .topviews-item');
                    const container = btn.closest('.menu-container');
                    const menu = container._hoistedMenu || container.querySelector('.glass-menu');
                    const isShowing = menu.classList.contains('show');

                    // Close all other open menus
                    closeAllMenus();

                    if (!isShowing) {
                        const menuWidth = 210;
                        const btnRect = btn.getBoundingClientRect();
                        const viewportWidth = window.innerWidth;
                        const inScrollRow = btn.closest('.shelf-scroll-row');
                        const inSongRow = btn.closest('.song-row');

                        if (inScrollRow || inSongRow) {
                            // Move menu to <body> — completely escapes overflow:clip AND any
                            // transform-based containing blocks (.page-entrance animation, etc.)
                            menu._origParent = container;
                            container._hoistedMenu = menu;
                            document.body.appendChild(menu);
                            menu.style.position = 'fixed';
                            menu.style.zIndex = '9999';
                            menu.style.top = (btnRect.top - 10) + 'px';
                            if (btnRect.right + menuWidth > viewportWidth - 20) {
                                menu.style.left = (btnRect.left - menuWidth - 15) + 'px';
                            } else {
                                menu.style.left = (btnRect.right + 15) + 'px';
                            }
                            menu.style.transform = 'none';
                            menu.classList.remove('open-left');
                        } else {
                            if (btnRect.right + menuWidth > viewportWidth - 20) {
                                menu.classList.add('open-left');
                            } else {
                                menu.classList.remove('open-left');
                            }
                        }

                        menu.classList.add('show');
                        if (card) card.classList.add('menu-active');
                    }
                } else if (e.target.closest('.glass-menu-item')) {
                    // Clicking a menu item closes the menu after the item's action runs
                    closeAllMenus();
                } else if (!e.target.closest('.glass-menu')) {
                    closeAllMenus();
                }
            });

            initPageFeatures();
        });

        // REMOVED redundant/buggy link hijacker that was causing library tab redirect issues


        window.updateMarquees = updateMarquees;
        // Do not overwrite window.initPageFeatures if it was already set by a specifically loaded page script
        if (!window.initPageFeatures) {
            window.initPageFeatures = initPageFeatures;
        }
