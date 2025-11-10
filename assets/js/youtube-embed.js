/**
 * VN YouTube Embed JavaScript
 */

(function($) {
    'use strict';

    /**
     * Main YouTube Embed Handler
     */
    class VNYouTubeEmbed {
        constructor() {
            this.players = [];
            this.options = window.vnYouTubeEmbed?.options || {};
            this.lightbox = null;
            this.activeIframes = new Map(); // Track active iframes with their video IDs
            this.init();
        }

        /**
         * Initialize the handler
         */
        init() {
            this.bindEvents();
            this.initPlayers();
            this.setupIntersectionObserver();
        }

        /**
         * Bind events
         */
        bindEvents() {
            $(document).on('click', '.vn-youtube-player', this.handlePlayerClick.bind(this));
            $(document).on('click', '.vn-youtube-lightbox, .vn-ytb-lightbox-close', this.closeLightbox.bind(this));
            $(document).on('click', '.vn-youtube-lightbox .vn-youtube-lightbox-content', function(e) {
                // Prevent closing when clicking inside content.
                e.stopPropagation();
            });
            $(document).on('click', '.vn-youtube-control-button', this.handleControlButtonClick.bind(this));
            // Support for custom buttons/links with data attributes
            $(document).on('click', '[data-vn-youtube-control]', this.handleCustomControlClick.bind(this));
            // Support for links with href containing video parameters
            $(document).on('click', 'a[href*="video="], a[href*="#play"], a[href*="#youtube"]', this.handleHrefControlClick.bind(this));
            $(window).on('resize', this.debounce(this.handleResize.bind(this), 250));
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.closeLightbox();
                }
            });
        }

        /**
         * Initialize all players on the page
         */
        initPlayers() {
            $('.vn-youtube-player').each((index, element) => {
                this.initPlayer($(element));
            });
        }

        /**
         * Initialize a single player
         */
        initPlayer($player) {
            const videoId = $player.data('video-id');
            const autoplay = $player.data('autoplay') === 1;
            const customThumb = $player.data('custom-thumb') === 'true';
            
            if (!videoId) {
                this.showError($player, 'Invalid video ID');
                return;
            }

            // Store player data
            this.players.push({
                element: $player[0],
                videoId: videoId,
                autoplay: autoplay,
                customThumb: customThumb,
                loaded: false
            });

            // Load thumbnail if lazy loading is enabled
            if ($player.hasClass('lazy-load-enabled')) {
                this.setupLazyLoading($player);
            } else {
                this.loadThumbnail($player);
            }
        }

        /**
         * Setup lazy loading for thumbnails
         */
        setupLazyLoading($player) {
            const img = $player.find('img');
            if (img.length && 'IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.loadThumbnail($player);
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    rootMargin: '50px 0px',
                    threshold: 0.1
                });

                observer.observe($player[0]);
            } else {
                // Fallback for browsers without IntersectionObserver
                this.loadThumbnail($player);
            }
        }

        /**
         * Load thumbnail image
         */
        loadThumbnail($player) {
            const img = $player.find('img');
            
            if (img.length && !img.hasClass('loaded')) {
                img.on('load', function() {
                    $(this).addClass('loaded');
                });

                img.on('error', function() {
                    // Fallback to lower quality thumbnail
                    const src = $(this).attr('src');
                    if (src && src.includes('maxresdefault')) {
                        $(this).attr('src', src.replace('maxresdefault', 'hqdefault'));
                    }
                });

                // Trigger load if src is already set
                if (img.attr('src')) {
                    img.trigger('load');
                }
            }
        }

        /**
         * Handle player click
         */
        handlePlayerClick(event) {
            event.preventDefault();
            const $player = $(event.currentTarget);
            
            if ($player.hasClass('loading') || $player.find('iframe').length) {
                return;
            }

            const useLightbox = ($player.data('lightbox') === 1) || $player.hasClass('use-lightbox');
            if (useLightbox) {
                this.openLightbox($player.data('video-id'), $player.data('autoplay') === 1);
            } else {
                this.loadVideo($player);
            }
        }

        /**
         * Load and play video
         */
        loadVideo($player) {
            const videoId = $player.data('video-id');
            const autoplay = $player.data('autoplay') === 1;

            if (!videoId) {
                this.showError($player, 'No video ID found');
                return;
            }

            // Show loading state
            $player.addClass('loading');

            // Build iframe
            const iframe = this.createIframe(videoId, autoplay);
            
            // Replace thumbnail with iframe
            setTimeout(() => {
                $player.removeClass('loading');
                $player.find('.vn-youtube-thumbnail').replaceWith(iframe);
                
                // Trigger custom event
                $player.trigger('vnYouTubeLoaded', {
                    videoId: videoId,
                    autoplay: autoplay
                });
            }, 300);
        }

        /**
         * Create YouTube iframe
         */
        createIframe(videoId, autoplay = true, startTime = 0, endTime = 0) {
            const params = new URLSearchParams({
                autoplay: autoplay ? 1 : 0,
                rel: 0,
                showinfo: 0,
                modestbranding: 1,
                playsinline: 1,
                enablejsapi: 1 // Enable JS API for time control
            });

            // Add start time if specified
            if (startTime > 0) {
                params.set('start', startTime);
            }

            // Add end time if specified
            if (endTime > 0) {
                params.set('end', endTime);
            }

            const src = `https://www.youtube.com/embed/${videoId}?${params.toString()}`;
            
            const iframe = $('<iframe>')
                .attr('src', src)
                .attr('frameborder', '0')
                .attr('allowfullscreen', true)
                .attr('allow', 'autoplay; encrypted-media; picture-in-picture')
                .attr('title', `YouTube video ${videoId}`)
                .attr('data-video-id', videoId);

            return iframe;
        }

        /**
         * Build and open lightbox overlay with iframe.
         */
        openLightbox(videoId, autoplay = true) {
            if (!videoId) return;

            // Create lightbox container if not exists.
            if (!this.lightbox) {
                this.lightbox = $(
                    '<div class="vn-youtube-lightbox" role="dialog" aria-modal="true" aria-label="YouTube video lightbox">' +
                        '<div class="vn-youtube-lightbox-content">' +
                            '<button class="vn-ytb-lightbox-close" aria-label="Close">&times;</button>' +
                            '<div class="vn-youtube-lightbox-player"></div>' +
                        '</div>' +
                    '</div>'
                ).appendTo('body');
            }

            // Inject iframe
            const $playerWrap = this.lightbox.find('.vn-youtube-lightbox-player');
            $playerWrap.empty().append(this.createIframe(videoId, autoplay));

            // Show
            this.lightbox.addClass('is-active');
            $('body').addClass('vn-youtube-no-scroll');
        }

        /**
         * Close lightbox and cleanup.
         */
        closeLightbox() {
            if (!this.lightbox) return;
            this.lightbox.removeClass('is-active');
            $('body').removeClass('vn-youtube-no-scroll');
            // Stop video by removing iframe
            const $playerWrap = this.lightbox.find('.vn-youtube-lightbox-player');
            $playerWrap.empty();
        }

        /**
         * Show error state
         */
        showError($player, message) {
            console.error('VN YouTube Embed Error:', message);
            $player.addClass('error');
        }

        /**
         * Handle window resize
         */
        handleResize() {
            // Recalculate any responsive elements if needed
            $('.vn-youtube-player iframe').each(function() {
                // Maintain aspect ratio on resize
                const $iframe = $(this);
                const $container = $iframe.closest('.vn-youtube-player');
                // Additional resize logic can be added here
            });
        }

        /**
         * Setup intersection observer for performance
         */
        setupIntersectionObserver() {
            if (!('IntersectionObserver' in window)) {
                return;
            }

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const $player = $(entry.target);
                    if (entry.isIntersecting) {
                        $player.addClass('in-viewport');
                    } else {
                        $player.removeClass('in-viewport');
                    }
                });
            }, {
                threshold: 0.1
            });

            $('.vn-youtube-player').each(function() {
                observer.observe(this);
            });
        }

        /**
         * Debounce utility function
         */
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        /**
         * Handle control button click
         */
        handleControlButtonClick(event) {
            event.preventDefault();
            const $button = $(event.currentTarget);
            
            const targetId = $button.data('target-id');
            const videoId = $button.data('video-id');
            const startTime = parseInt($button.data('start')) || 0;
            const endTime = parseInt($button.data('end')) || 0;
            const autoplay = $button.data('autoplay') === 1;

            if (targetId) {
                // Control existing player by ID
                this.playVideoSegment(targetId, startTime, endTime, autoplay);
            } else if (videoId) {
                // Find player by video ID or create in lightbox
                const $player = $(`.vn-youtube-player[data-video-id="${videoId}"]`).first();
                
                if ($player.length) {
                    this.playVideoSegment($player.attr('id'), startTime, endTime, autoplay);
                } else {
                    // Play in lightbox if no player found
                    this.openLightboxWithSegment(videoId, startTime, endTime, autoplay);
                }
            }
        }

        /**
         * Handle custom control click (for any button/link with data attributes)
         */
        handleCustomControlClick(event) {
            event.preventDefault();
            const $element = $(event.currentTarget);
            
            // Read data attributes
            const config = {
                videoId: $element.attr('data-vn-youtube-control') || $element.data('video-id'),
                targetId: $element.data('target-id') || $element.data('target'),
                start: parseInt($element.data('start') || $element.data('time-start') || 0),
                end: parseInt($element.data('end') || $element.data('time-end') || 0),
                autoplay: $element.data('autoplay') !== false && $element.data('autoplay') !== 'false'
            };

            // Validate
            if (!config.videoId && !config.targetId) {
                console.error('VN YouTube Embed: No video ID or target ID specified');
                return;
            }

            // Add active state
            $element.addClass('vn-youtube-active');
            setTimeout(() => $element.removeClass('vn-youtube-active'), 300);

            // Execute control
            if (config.targetId) {
                this.playVideoSegment(config.targetId, config.start, config.end, config.autoplay);
            } else if (config.videoId) {
                const $player = $(`.vn-youtube-player[data-video-id="${config.videoId}"]`).first();
                
                if ($player.length) {
                    this.playVideoSegment($player.attr('id'), config.start, config.end, config.autoplay);
                } else {
                    this.openLightboxWithSegment(config.videoId, config.start, config.end, config.autoplay);
                }
            }
        }

        /**
         * Handle link href control click (for links with video parameters in URL)
         */
        handleHrefControlClick(event) {
            const $link = $(event.currentTarget);
            const href = $link.attr('href');
            
            if (!href) return;

            // Parse video parameters from href
            const config = this.parseVideoHref(href);
            
            // Only handle if valid video config found
            if (!config.videoId && !config.targetId) {
                return; // Let default link behavior happen
            }

            // Prevent default navigation
            event.preventDefault();

            // Add active state
            $link.addClass('vn-youtube-active');
            setTimeout(() => $link.removeClass('vn-youtube-active'), 300);

            // Execute control
            if (config.targetId) {
                this.playVideoSegment(config.targetId, config.start, config.end, config.autoplay);
            } else if (config.videoId) {
                const $player = $(`.vn-youtube-player[data-video-id="${config.videoId}"]`).first();
                
                if ($player.length) {
                    this.playVideoSegment($player.attr('id'), config.start, config.end, config.autoplay);
                } else {
                    this.openLightboxWithSegment(config.videoId, config.start, config.end, config.autoplay);
                }
            }
        }

        /**
         * Parse video parameters from href URL
         * Supports formats:
         * - #play?video=ID&start=30&end=60
         * - #youtube?video=ID&start=30
         * - ?video=ID&start=30&end=60
         * - #vn-youtube-ID-1234 (target player)
         */
        parseVideoHref(href) {
            const config = {
                videoId: null,
                targetId: null,
                start: 0,
                end: 0,
                autoplay: true
            };

            // Check for target player ID format: #vn-youtube-VIDEO_ID-XXXX
            if (href.match(/#vn-youtube-[a-zA-Z0-9_-]+-\d+/)) {
                config.targetId = href.replace('#', '');
                
                // Try to extract time params after the ID
                const timeMatch = href.match(/[?&](start|time)=(\d+)/);
                if (timeMatch) {
                    config.start = parseInt(timeMatch[2]);
                }
                const endMatch = href.match(/[?&]end=(\d+)/);
                if (endMatch) {
                    config.end = parseInt(endMatch[1]);
                }
                
                return config;
            }

            // Extract query string (after ? or after #something?)
            let queryString = '';
            
            if (href.includes('?')) {
                queryString = href.split('?')[1];
            } else if (href.includes('#') && href.split('#')[1].includes('=')) {
                // Handle #youtube or #play as anchors
                queryString = href.split('#')[1];
            }

            if (!queryString) return config;

            // Parse query parameters
            const params = new URLSearchParams(queryString);
            
            // Get video ID (support multiple param names)
            config.videoId = params.get('video') || 
                           params.get('v') || 
                           params.get('id') || 
                           params.get('videoid');

            // Get start time (support multiple param names)
            const startParam = params.get('start') || 
                             params.get('time') || 
                             params.get('t') || 
                             params.get('time-start');
            if (startParam) {
                config.start = parseInt(startParam);
            }

            // Get end time
            const endParam = params.get('end') || params.get('time-end');
            if (endParam) {
                config.end = parseInt(endParam);
            }

            // Get target player ID
            config.targetId = params.get('target') || params.get('player');

            // Get autoplay setting
            const autoplayParam = params.get('autoplay');
            if (autoplayParam !== null) {
                config.autoplay = autoplayParam !== 'false' && autoplayParam !== '0';
            }

            return config;
        }

        /**
         * Play video segment with start and end times
         */
        playVideoSegment(playerId, startTime = 0, endTime = 0, autoplay = true) {
            const $player = $(`#${playerId}`);
            if (!$player.length) {
                console.error('VN YouTube Embed: Player not found:', playerId);
                return;
            }

            const videoId = $player.data('video-id');
            if (!videoId) {
                console.error('VN YouTube Embed: No video ID found');
                return;
            }

            // Check if iframe already exists
            let $iframe = $player.find('iframe');
            
            if ($iframe.length) {
                // Update existing iframe with new time parameters
                const currentSrc = $iframe.attr('src');
                const url = new URL(currentSrc);
                
                url.searchParams.set('autoplay', autoplay ? '1' : '0');
                if (startTime > 0) {
                    url.searchParams.set('start', startTime);
                } else {
                    url.searchParams.delete('start');
                }
                if (endTime > 0) {
                    url.searchParams.set('end', endTime);
                } else {
                    url.searchParams.delete('end');
                }
                
                // Reload iframe with new parameters
                $iframe.attr('src', url.toString());
            } else {
                // Create new iframe with segment parameters
                const iframe = this.createIframe(videoId, autoplay, startTime, endTime);
                $player.find('.vn-youtube-thumbnail').replaceWith(iframe);
            }

            // Store active iframe reference
            if ($iframe.length) {
                this.activeIframes.set(playerId, {
                    videoId: videoId,
                    startTime: startTime,
                    endTime: endTime
                });
            }

            // Scroll to player if needed
            this.scrollToPlayer($player);
        }

        /**
         * Open lightbox with video segment
         */
        openLightboxWithSegment(videoId, startTime = 0, endTime = 0, autoplay = true) {
            if (!videoId) return;

            // Create lightbox container if not exists
            if (!this.lightbox) {
                this.lightbox = $(
                    '<div class="vn-youtube-lightbox" role="dialog" aria-modal="true" aria-label="YouTube video lightbox">' +
                        '<div class="vn-youtube-lightbox-content">' +
                            '<button class="vn-ytb-lightbox-close" aria-label="Close">&times;</button>' +
                            '<div class="vn-youtube-lightbox-player"></div>' +
                        '</div>' +
                    '</div>'
                ).appendTo('body');
            }

            // Inject iframe with segment parameters
            const $playerWrap = this.lightbox.find('.vn-youtube-lightbox-player');
            $playerWrap.empty().append(this.createIframe(videoId, autoplay, startTime, endTime));

            // Show lightbox
            this.lightbox.addClass('is-active');
            $('body').addClass('vn-youtube-no-scroll');
        }

        /**
         * Scroll to player smoothly
         */
        scrollToPlayer($player) {
            if (!$player.length) return;

            const offset = 100; // Offset from top
            const playerTop = $player.offset().top - offset;
            
            $('html, body').animate({
                scrollTop: playerTop
            }, 500);
        }

        /**
         * Public API methods
         */
        loadVideoById(videoId, containerId) {
            const $container = $(`#${containerId}`);
            if ($container.length) {
                $container.data('video-id', videoId);
                this.loadVideo($container);
            }
        }

        playSegment(playerId, startTime, endTime) {
            this.playVideoSegment(playerId, startTime, endTime, true);
        }

        getAllPlayers() {
            return this.players;
        }

        getPlayerByVideoId(videoId) {
            return this.players.find(player => player.videoId === videoId);
        }

        getActiveIframes() {
            return this.activeIframes;
        }
    }

    /**
     * Initialize when DOM is ready
     */
    $(document).ready(function() {
        window.vnYouTubeEmbedInstance = new VNYouTubeEmbed();
        
        // Expose init function for dynamic content
        window.vnYouTubeEmbedInit = function() {
            if (window.vnYouTubeEmbedInstance) {
                window.vnYouTubeEmbedInstance.initPlayers();
            }
        };
    });

    /**
     * Handle dynamic content loading (AJAX, etc.)
     */
    $(document).ajaxComplete(function() {
        if (window.vnYouTubeEmbedInstance) {
            setTimeout(() => {
                window.vnYouTubeEmbedInstance.initPlayers();
            }, 100);
        }
    });

})(jQuery);
