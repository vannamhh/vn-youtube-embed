/**
 * VN YouTube Embed Admin JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        /**
         * Clear cache functionality
         */
        $('#vn-clear-cache').on('click', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const originalText = $button.text();
            
            // Disable button and show loading
            $button.prop('disabled', true).text('Clearing...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'vn_clear_thumbnail_cache',
                    nonce: window.vnYouTubeEmbed?.nonce || ''
                },
                success: function(response) {
                    if (response.success) {
                        $button.text('Cache Cleared!');
                        
                        // Show success message
                        $('<div class="notice notice-success is-dismissible"><p>' + 
                          (response.data.message || 'Cache cleared successfully.') + 
                          '</p></div>')
                        .insertAfter('.wrap h1');
                        
                        // Update cache stats if available
                        $('.vn-youtube-cache-info p').html(
                            'Cached thumbnails: 0 files (0 B)'
                        );
                        
                        // Reset button after 2 seconds
                        setTimeout(function() {
                            $button.prop('disabled', false).text(originalText);
                        }, 2000);
                        
                    } else {
                        $button.text('Error!');
                        console.error('Cache clear failed:', response);
                        
                        setTimeout(function() {
                            $button.prop('disabled', false).text(originalText);
                        }, 2000);
                    }
                },
                error: function(xhr, status, error) {
                    $button.text('Error!');
                    console.error('AJAX error:', error);
                    
                    setTimeout(function() {
                        $button.prop('disabled', false).text(originalText);
                    }, 2000);
                }
            });
        });

        /**
         * Settings form enhancements
         */
        
        // Show/hide cache duration field based on settings
        function toggleCacheDuration() {
            const $cacheDuration = $('input[name="vn_youtube_embed_options[cache_duration]"]').closest('tr');
            // Add any conditional logic here if needed
        }

        // Quality preview
        $('select[name="vn_youtube_embed_options[thumbnail_quality]"]').on('change', function() {
            const quality = $(this).val();
            const descriptions = {
                'maxresdefault': 'Best quality but largest file size. May not be available for all videos.',
                'sddefault': 'Good balance between quality and file size.',
                'hqdefault': 'Decent quality with smaller file size.',
                'mqdefault': 'Lower quality but fastest loading.'
            };
            
            const $description = $(this).siblings('.description');
            if (descriptions[quality]) {
                $description.text(descriptions[quality]);
            }
        });

        // Validate cache duration
        $('input[name="vn_youtube_embed_options[cache_duration]"]').on('input', function() {
            const value = parseInt($(this).val());
            const $this = $(this);
            
            if (value < 1 || value > 365 || isNaN(value)) {
                $this.css('border-color', '#dc3232');
                if (!$this.siblings('.error-message').length) {
                    $this.after('<span class="error-message" style="color: #dc3232; font-size: 12px; display: block;">Please enter a value between 1 and 365.</span>');
                }
            } else {
                $this.css('border-color', '');
                $this.siblings('.error-message').remove();
            }
        });

        /**
         * Copy shortcode examples
         */
        $('.vn-youtube-usage code').on('click', function() {
            const text = $(this).text();
            
            // Try to use the Clipboard API
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopyFeedback($(this));
                }).catch(function() {
                    fallbackCopyTextToClipboard(text, $(this));
                });
            } else {
                fallbackCopyTextToClipboard(text, $(this));
            }
        });

        /**
         * Fallback copy to clipboard
         */
        function fallbackCopyTextToClipboard(text, $element) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                showCopyFeedback($element);
            } catch (err) {
                console.error('Fallback: Oops, unable to copy', err);
            }
            
            document.body.removeChild(textArea);
        }

        /**
         * Show copy feedback
         */
        function showCopyFeedback($element) {
            const $feedback = $('<span class="copy-feedback">Copied!</span>');
            $feedback.css({
                'position': 'absolute',
                'background': '#46b450',
                'color': 'white',
                'padding': '4px 8px',
                'border-radius': '3px',
                'font-size': '12px',
                'z-index': '9999',
                'margin-left': '10px'
            });
            
            $element.after($feedback);
            
            setTimeout(function() {
                $feedback.fadeOut(function() {
                    $feedback.remove();
                });
            }, 2000);
        }

        /**
         * Add hover effect to code examples
         */
        $('.vn-youtube-usage code').css({
            'cursor': 'pointer',
            'position': 'relative'
        }).attr('title', 'Click to copy');

        $('.vn-youtube-usage code').hover(
            function() {
                $(this).css('background-color', '#e5e5e5');
            },
            function() {
                $(this).css('background-color', '#f5f5f5');
            }
        );

        // Initialize on page load
        toggleCacheDuration();
    });

})(jQuery);
