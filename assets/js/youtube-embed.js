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
        createIframe(videoId, autoplay = true) {
            const params = new URLSearchParams({
                autoplay: autoplay ? 1 : 0,
                rel: 0,
                showinfo: 0,
                modestbranding: 1,
                playsinline: 1
            });

            const src = `https://www.youtube.com/embed/${videoId}?${params.toString()}`;
            
            return $('<iframe>')
                .attr('src', src)
                .attr('frameborder', '0')
                .attr('allowfullscreen', true)
                .attr('allow', 'autoplay; encrypted-media; picture-in-picture')
                .attr('title', `YouTube video ${videoId}`);
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
         * Public API methods
         */
        loadVideoById(videoId, containerId) {
            const $container = $(`#${containerId}`);
            if ($container.length) {
                $container.data('video-id', videoId);
                this.loadVideo($container);
            }
        }

        getAllPlayers() {
            return this.players;
        }

        getPlayerByVideoId(videoId) {
            return this.players.find(player => player.videoId === videoId);
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
