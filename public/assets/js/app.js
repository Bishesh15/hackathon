/* ================================================================
   LearnAI – Frontend JS (jQuery)  v2
   ================================================================ */

var App = (function ($) {
    'use strict';

    var API = '../api';
    var chatConversationId = 0;

    // ── Helpers ──────────────────────────────────────────────────

    function post(url, data) {
        return $.ajax({
            url: API + url,
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json'
        });
    }

    function get(url) {
        return $.ajax({ url: API + url, method: 'GET', dataType: 'json' });
    }

    function showMsg(selector, text, isError) {
        var el = $(selector);
        if (isError) {
            el.html('<div class="msg msg-error">' + text + '</div>');
        } else if (text) {
            el.html('<div class="msg msg-success">' + text + '</div>');
        } else {
            el.html('');
        }
    }

    function btnLoading(btn, loading) {
        if (loading) {
            btn.data('orig', btn.html()).prop('disabled', true)
               .html('<span class="spinner"></span> Please wait…');
        } else {
            btn.prop('disabled', false).html(btn.data('orig'));
        }
    }

    // Simple markdown→HTML (bold, italic, code, lists, headings, links)
    function md(text) {
        if (!text) return '';
        var h = text
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            // Code blocks
            .replace(/```(\w*)\n([\s\S]*?)```/g, '<pre><code>$2</code></pre>')
            // Inline code
            .replace(/`([^`]+)`/g, '<code>$1</code>')
            // Bold
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            // Italic
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            // Headings
            .replace(/^### (.+)$/gm, '<h3>$1</h3>')
            .replace(/^## (.+)$/gm, '<h2>$1</h2>')
            .replace(/^# (.+)$/gm, '<h1>$1</h1>')
            // Unordered list
            .replace(/^\- (.+)$/gm, '<li>$1</li>')
            .replace(/^\* (.+)$/gm, '<li>$1</li>')
            // Numbered list
            .replace(/^\d+\. (.+)$/gm, '<li>$1</li>')
            // Wrap consecutive <li> in <ul>
            .replace(/(<li>.*<\/li>\n?)+/g, '<ul>$&</ul>')
            // Paragraphs (double newline)
            .replace(/\n\n/g, '</p><p>')
            // Single newline → <br>
            .replace(/\n/g, '<br>');
        return '<p>' + h + '</p>';
    }

    // ── Auth ─────────────────────────────────────────────────────

    $(document).on('submit', '#register-form', function (e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btnLoading(btn, true);

        post('/auth/register.php', {
            name: $(this).find('[name="name"]').val(),
            email: $(this).find('[name="email"]').val(),
            password: $(this).find('[name="password"]').val()
        }).done(function (r) {
            showMsg('#form-msg', r.message, false);
            setTimeout(function () { location.href = 'dashboard.php'; }, 400);
        }).fail(function (xhr) {
            showMsg('#form-msg', xhr.responseJSON ? xhr.responseJSON.message : 'Error', true);
            btnLoading(btn, false);
        });
    });

    $(document).on('submit', '#login-form', function (e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btnLoading(btn, true);

        post('/auth/login.php', {
            email: $(this).find('[name="email"]').val(),
            password: $(this).find('[name="password"]').val()
        }).done(function (r) {
            showMsg('#form-msg', r.message, false);
            setTimeout(function () { location.href = 'dashboard.php'; }, 400);
        }).fail(function (xhr) {
            showMsg('#form-msg', xhr.responseJSON ? xhr.responseJSON.message : 'Error', true);
            btnLoading(btn, false);
        });
    });

    // ── Dashboard ────────────────────────────────────────────────

    function loadDashboard() {
        get('/dashboard/summary.php').done(function (r) {
            var s = r.stats;
            $('#s-activities').text(s.total_activities);
            $('#s-tutor').text(s.tutor_sessions);
            $('#s-notes').text(s.notes_generated);
            $('#s-quiz').text(s.quizzes_taken);
            $('#s-tests').text(s.tests_attempted);
            $('#s-avg').text(s.avg_score + '%');
        });

        // Hero search → tutor
        $('#hero-search-btn').on('click', function () {
            var q = $('#hero-search').val().trim();
            if (q) location.href = 'tutor.php?q=' + encodeURIComponent(q);
        });
        $('#hero-search').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $('#hero-search-btn').click();
            }
        });
        // Quick topics
        $(document).on('click', '.quick-topic', function () {
            location.href = 'tutor.php?q=' + encodeURIComponent($(this).data('topic'));
        });
    }

    // ── Notes module ─────────────────────────────────────────────

    $(document).on('submit', '#module-form', function (e) {
        e.preventDefault();
        var form = $(this);
        var btn = form.find('button[type="submit"]');
        var module = form.data('module');
        var topic = form.find('[name="topic"]').val();

        btnLoading(btn, true);
        $('#module-result').hide();

        post('/module/run.php', { module: module, topic: topic })
            .done(function (r) {
                $('#module-result').html(md(r.content)).show();
                showMsg('#form-msg', '', false);
            })
            .fail(function (xhr) {
                showMsg('#form-msg', xhr.responseJSON ? xhr.responseJSON.message : 'Error', true);
            })
            .always(function () { btnLoading(btn, false); });
    });

    // ================================================================
    // CHAT (AI Tutor – ChatGPT-style continuous conversation)
    // ================================================================

    function initChat(conversationId) {
        chatConversationId = conversationId || 0;

        // If URL has ?q= param, auto-send first message
        var params = new URLSearchParams(window.location.search);
        var initQ = params.get('q');

        if (chatConversationId > 0) {
            // Load existing conversation
            loadConversation(chatConversationId);
        } else if (initQ) {
            sendChatMessage(initQ);
        }

        // Send button
        $('#chat-send').on('click', function () {
            var msg = $('#chat-input').val().trim();
            if (msg) sendChatMessage(msg);
        });

        // Enter to send (Shift+Enter for newline)
        $('#chat-input').on('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                var msg = $(this).val().trim();
                if (msg) sendChatMessage(msg);
            }
        });

        // Auto-resize textarea
        $('#chat-input').on('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 150) + 'px';
        });
    }

    function sendChatMessage(message) {
        // Hide welcome
        $('#chat-welcome').hide();

        // Append user bubble
        appendChatBubble('user', message);
        $('#chat-input').val('').css('height', 'auto');
        $('#chat-send').prop('disabled', true);

        // Show typing indicator
        var typingHtml = '<div class="chat-message assistant" id="typing-indicator">'
            + '<div class="msg-avatar">AI</div>'
            + '<div class="msg-content"><div class="msg-bubble"><div class="typing-indicator"><span></span><span></span><span></span></div></div></div>'
            + '</div>';
        $('#chat-messages').append(typingHtml);
        scrollChat();

        post('/chat/send.php', {
            message: message,
            conversation_id: chatConversationId
        }).done(function (r) {
            chatConversationId = r.conversation_id;
            $('#typing-indicator').remove();
            appendChatBubble('assistant', r.reply);
            scrollChat();
        }).fail(function (xhr) {
            $('#typing-indicator').remove();
            appendChatBubble('assistant', 'Sorry, something went wrong. Please try again.');
        }).always(function () {
            $('#chat-send').prop('disabled', false);
            $('#chat-input').focus();
        });
    }

    function appendChatBubble(role, content) {
        var avatar = role === 'assistant' ? 'AI' : 'You';
        var bubbleContent = role === 'assistant' ? md(content) : $('<div>').text(content).html();
        var html = '<div class="chat-message ' + role + '">'
            + '<div class="msg-avatar">' + avatar + '</div>'
            + '<div class="msg-content"><div class="msg-bubble">' + bubbleContent + '</div></div>'
            + '</div>';
        $('#chat-messages').append(html);
        scrollChat();
    }

    function loadConversation(convId) {
        get('/chat/messages.php?id=' + convId).done(function (r) {
            $('#chat-welcome').hide();
            $('#chat-messages').empty();
            $.each(r.messages, function (i, msg) {
                appendChatBubble(msg.role, msg.content);
            });
            scrollChat();
        });
    }

    function scrollChat() {
        var el = document.getElementById('chat-messages');
        if (el) el.scrollTop = el.scrollHeight;
    }

    // ================================================================
    // QUIZ (MCQ – was Test, now renamed)
    // ================================================================

    var quizState = { testId: 0, questions: [] };

    $(document).on('submit', '#quiz-form', function (e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btnLoading(btn, true);

        var topic = $('#quiz-topic').val();
        var count = parseInt($('#quiz-count').val(), 10);

        post('/test/create.php', { topic: topic, count: count })
            .done(function (r) {
                quizState.testId = r.test_id;
                quizState.questions = r.questions;
                renderQuizPaper(r.questions, r.topic);
            })
            .fail(function (xhr) {
                showMsg('#quiz-msg', xhr.responseJSON ? xhr.responseJSON.message : 'Error', true);
            })
            .always(function () { btnLoading(btn, false); });
    });

    function renderQuizPaper(questions, topic) {
        var html = '';
        $.each(questions, function (i, q) {
            html += '<div class="question-card card">';
            html += '<div class="q-number">Question ' + (i + 1) + '</div>';
            html += '<div class="q-text">' + q.question + '</div>';
            $.each(q.options, function (j, opt) {
                var letter = String.fromCharCode(65 + j);
                html += '<label class="option-label" data-q="' + i + '" data-val="' + letter + '">';
                html += '<input type="radio" name="q' + i + '" value="' + letter + '"> ' + opt;
                html += '</label>';
            });
            html += '</div>';
        });

        $('#quiz-title').text('Quiz: ' + topic);
        $('#quiz-questions').html(html);
        $('#quiz-setup').hide();
        $('#quiz-paper').show();
    }

    // Highlight selected option
    $(document).on('change', '.option-label input[type="radio"]', function () {
        var qIndex = $(this).closest('.option-label').data('q');
        $('[data-q="' + qIndex + '"]').removeClass('selected');
        $(this).closest('.option-label').addClass('selected');
    });

    $(document).on('click', '#quiz-submit-btn', function () {
        var btn = $(this);
        btnLoading(btn, true);

        var answers = {};
        quizState.questions.forEach(function (q, i) {
            answers[i] = $('input[name="q' + i + '"]:checked').val() || '';
        });

        post('/test/submit.php', { test_id: quizState.testId, answers: answers })
            .done(function (r) {
                sessionStorage.setItem('last_result', JSON.stringify(r));
                sessionStorage.setItem('attempt_id', r.attempt_id);
                sessionStorage.setItem('last_quiz_topic', $('#quiz-topic').val());
                showQuizResult(r);
            })
            .fail(function (xhr) {
                showMsg('#quiz-msg', xhr.responseJSON ? xhr.responseJSON.message : 'Error', true);
            })
            .always(function () { btnLoading(btn, false); });
    });

    function showQuizResult(r) {
        $('#quiz-paper').hide();
        $('#quiz-score').text(r.percentage + '%');
        $('#quiz-score-label').text(r.score + ' / ' + r.total + ' correct');
        $('#quiz-result').show();
    }

    // Quiz analysis
    $(document).on('click', '#quiz-analysis-btn', function () {
        var btn = $(this);
        btnLoading(btn, true);
        var attemptId = parseInt(sessionStorage.getItem('attempt_id'), 10);

        post('/analysis/get.php', { attempt_id: attemptId })
            .done(function (r) {
                var a = r.analysis;
                var html = '';
                if (a.weaknesses && a.weaknesses.length) {
                    html += '<h3>Weaknesses</h3><ul>';
                    $.each(a.weaknesses, function (i, w) { html += '<li>' + w + '</li>'; });
                    html += '</ul>';
                }
                if (a.insights && a.insights.length) {
                    html += '<h3>Insights</h3><ul>';
                    $.each(a.insights, function (i, w) { html += '<li>' + w + '</li>'; });
                    html += '</ul>';
                }
                if (a.recommendations && a.recommendations.length) {
                    html += '<h3>Recommendations</h3><ul>';
                    $.each(a.recommendations, function (i, w) { html += '<li>' + w + '</li>'; });
                    html += '</ul>';
                }
                $('#quiz-analysis').html(html);
                $('#quiz-analysis-box').show();
                var topic = sessionStorage.getItem('last_quiz_topic') || 'Quiz';
                savePlanToBackend('analysis', topic, a, 'quiz', attemptId, null);
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error getting analysis');
            })
            .always(function () { btnLoading(btn, false); });
    });

    // Quiz study plan
    $(document).on('click', '#quiz-plan-btn', function () {
        var btn = $(this);
        btnLoading(btn, true);
        var attemptId = parseInt(sessionStorage.getItem('attempt_id'), 10);

        post('/plan/get.php', { attempt_id: attemptId })
            .done(function (r) {
                var p = r.plan;
                var html = '<h3>' + (p.title || 'Study Plan') + '</h3>';
                if (p.days && p.days.length) {
                    $.each(p.days, function (i, day) {
                        html += '<div style="margin-bottom:1rem;"><strong>Day ' + day.day + ':</strong> ' + day.focus + '<ul>';
                        $.each(day.tasks || [], function (j, t) { html += '<li>' + t + '</li>'; });
                        html += '</ul></div>';
                    });
                }
                $('#quiz-plan-content').html(html);
                $('#quiz-plan-box').show();
                var topic = sessionStorage.getItem('last_quiz_topic') || 'Quiz';
                savePlanToBackend('study_plan', topic, p, 'quiz', attemptId, null);
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error getting plan');
            })
            .always(function () { btnLoading(btn, false); });
    });

    // Quiz retry
    $(document).on('click', '#quiz-retry-btn', function () {
        $('#quiz-result').hide();
        $('#quiz-analysis-box').hide();
        $('#quiz-plan-box').hide();
        $('#quiz-setup').show();
        $('#quiz-form')[0].reset();
    });

    // ================================================================
    // TEST CENTER (Long-answer + Image upload)
    // ================================================================

    var examState = { examId: 0, questions: [], topic: '' };

    $(document).on('submit', '#test-create-form', function (e) {
        e.preventDefault();
        var btn = $(this).find('button[type="submit"]');
        btnLoading(btn, true);

        var topic = $('#test-topic').val();
        var count = parseInt($('#test-count').val(), 10);

        post('/exam/create.php', { topic: topic, count: count })
            .done(function (r) {
                examState.examId = r.exam_id;
                examState.questions = r.questions;
                examState.topic = r.topic || topic;
                renderTestPaper(r.questions, r.topic);
            })
            .fail(function (xhr) {
                showMsg('#test-msg', xhr.responseJSON ? xhr.responseJSON.message : 'Error', true);
            })
            .always(function () { btnLoading(btn, false); });
    });

    function renderTestPaper(questions, topic) {
        var html = '';
        $.each(questions, function (i, q) {
            html += '<div class="test-question">';
            html += '<div class="q-text"><strong>Q' + (i + 1) + '.</strong> ' + q.question;
            if (q.type) html += ' <span style="color:#667eea; font-size:.8rem;">(' + q.type + ')</span>';
            html += '</div>';
            html += '<div class="form-group">';
            html += '<textarea name="answer_' + i + '" placeholder="Write your answer here…" rows="5"></textarea>';
            html += '</div>';
            html += '</div>';
        });

        $('#test-title').text('Test: ' + topic);
        $('#test-questions').html(html);
        $('#test-setup').hide();
        $('#test-paper').show();
    }



    // Submit test
    $(document).on('click', '#test-submit-btn', function () {
        var btn = $(this);
        btnLoading(btn, true);

        var answers = [];
        examState.questions.forEach(function (q, i) {
            var answerText = $('[name="answer_' + i + '"]').val() || '';
            answers.push({ answer: answerText });
        });

        post('/exam/submit.php', { exam_id: examState.examId, answers: answers })
            .done(function (r) {
                showTestFeedback(r);
            })
            .fail(function (xhr) {
                showMsg('#test-msg', xhr.responseJSON ? xhr.responseJSON.message : 'Error', true);
            })
            .always(function () { btnLoading(btn, false); });
    });

    function showTestFeedback(r) {
        $('#test-paper').hide();
        $('#test-score').text(Math.round(r.score) + '%');

        var fb = r.feedback;
        var html = '';
        if (fb.questions && fb.questions.length) {
            $.each(fb.questions, function (i, q) {
                html += '<div style="margin-bottom:1.5rem; padding:1rem; border-left:4px solid ' + (q.score >= 60 ? '#10b981' : '#ef4444') + '; background:#fafafa; border-radius:0 8px 8px 0;">';
                html += '<strong>Q' + (i + 1) + ' – Score: ' + q.score + '%</strong><br>';
                html += '<p>' + q.feedback + '</p>';
                html += '</div>';
            });
        }
        $('#test-feedback-content').html(html);
        $('#test-feedback').show();
    }

    // Test retry
    $(document).on('click', '#test-retry-btn', function () {
        $('#test-feedback').hide();
        $('#test-analysis-box').hide();
        $('#test-plan-box').hide();
        $('#test-setup').show();
        $('#test-create-form')[0].reset();
    });

    // ── Test Analysis ────────────────────────────────────────────
    $(document).on('click', '#test-analysis-btn', function () {
        var btn = $(this);
        btnLoading(btn, true);

        post('/exam-analysis/analysis.php', { exam_id: examState.examId })
            .done(function (r) {
                var a = r.analysis;
                var html = '';
                if (a.weaknesses && a.weaknesses.length) {
                    html += '<h3>Weaknesses</h3><ul>';
                    $.each(a.weaknesses, function (i, w) { html += '<li>' + w + '</li>'; });
                    html += '</ul>';
                }
                if (a.insights && a.insights.length) {
                    html += '<h3>Insights</h3><ul>';
                    $.each(a.insights, function (i, w) { html += '<li>' + w + '</li>'; });
                    html += '</ul>';
                }
                if (a.recommendations && a.recommendations.length) {
                    html += '<h3>Recommendations</h3><ul>';
                    $.each(a.recommendations, function (i, w) { html += '<li>' + w + '</li>'; });
                    html += '</ul>';
                }
                $('#test-analysis-content').html(html);
                $('#test-analysis-box').show();

                // Auto-save to My Plan
                savePlanToBackend('analysis', examState.topic, a, 'test', null, examState.examId);
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error getting analysis');
            })
            .always(function () { btnLoading(btn, false); });
    });

    // ── Test Study Plan ──────────────────────────────────────────
    $(document).on('click', '#test-plan-btn', function () {
        var btn = $(this);
        btnLoading(btn, true);

        post('/exam-analysis/plan.php', { exam_id: examState.examId })
            .done(function (r) {
                var p = r.plan;
                var html = '<h3>' + (p.title || 'Study Plan') + '</h3>';
                if (p.days && p.days.length) {
                    $.each(p.days, function (i, day) {
                        html += '<div style="margin-bottom:1rem;"><strong>Day ' + day.day + ':</strong> ' + day.focus + '<ul>';
                        $.each(day.tasks || [], function (j, t) { html += '<li>' + t + '</li>'; });
                        html += '</ul></div>';
                    });
                }
                $('#test-plan-content').html(html);
                $('#test-plan-box').show();

                // Auto-save to My Plan
                savePlanToBackend('study_plan', examState.topic, p, 'test', null, examState.examId);
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error getting study plan');
            })
            .always(function () { btnLoading(btn, false); });
    });

    // ================================================================
    // RESULT PAGE (legacy – for quiz results navigated via URL)
    // ================================================================

    function loadResult() {
        var raw = sessionStorage.getItem('last_result');
        if (!raw) { $('#score-display').text('No result found.'); return; }

        var r = JSON.parse(raw);
        $('#score-display').text(r.percentage + '%');
        $('#score-label').text(r.score + ' / ' + r.total + ' correct');

        // Show details
        var html = '';
        $.each(r.details, function (i, d) {
            var borderColor = d.is_correct ? '#10b981' : '#ef4444';
            html += '<div style="padding:1rem; border-left:4px solid ' + borderColor + '; margin-bottom:.5rem; background:#fafafa; border-radius:0 8px 8px 0;">';
            html += '<strong>Q' + (i + 1) + '.</strong> ' + d.question + '<br>';
            html += 'Your answer: <strong>' + d.given + '</strong> | Correct: <strong>' + d.correct + '</strong>';
            html += '</div>';
        });
        $('#details-list').html(html);
        $('#result-details').show();

        sessionStorage.setItem('attempt_id', r.attempt_id);
    }

    $(document).on('click', '#btn-analysis', function () {
        var btn = $(this);
        btnLoading(btn, true);
        var attemptId = parseInt(sessionStorage.getItem('attempt_id'), 10);

        post('/analysis/get.php', { attempt_id: attemptId })
            .done(function (r) {
                var a = r.analysis;
                var html = '';
                if (a.weaknesses && a.weaknesses.length) {
                    html += '<h3>Weaknesses</h3><ul>';
                    $.each(a.weaknesses, function (i, w) { html += '<li>' + w + '</li>'; });
                    html += '</ul>';
                }
                if (a.insights && a.insights.length) {
                    html += '<h3>Insights</h3><ul>';
                    $.each(a.insights, function (i, w) { html += '<li>' + w + '</li>'; });
                    html += '</ul>';
                }
                if (a.recommendations && a.recommendations.length) {
                    html += '<h3>Recommendations</h3><ul>';
                    $.each(a.recommendations, function (i, w) { html += '<li>' + w + '</li>'; });
                    html += '</ul>';
                }
                $('#analysis-content').html(html);
                $('#analysis-box').show();

                // Auto-save to My Plan
                var topic = sessionStorage.getItem('last_quiz_topic') || 'Quiz';
                savePlanToBackend('analysis', topic, a, 'quiz', attemptId, null);
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error getting analysis');
            })
            .always(function () { btnLoading(btn, false); });
    });

    $(document).on('click', '#btn-plan', function () {
        var btn = $(this);
        btnLoading(btn, true);
        var attemptId = parseInt(sessionStorage.getItem('attempt_id'), 10);

        post('/plan/get.php', { attempt_id: attemptId })
            .done(function (r) {
                var p = r.plan;
                var html = '<h3>' + (p.title || 'Study Plan') + '</h3>';
                if (p.days && p.days.length) {
                    $.each(p.days, function (i, day) {
                        html += '<div style="margin-bottom:1rem;"><strong>Day ' + day.day + ':</strong> ' + day.focus + '<ul>';
                        $.each(day.tasks || [], function (j, t) { html += '<li>' + t + '</li>'; });
                        html += '</ul></div>';
                    });
                }
                $('#plan-content').html(html);
                $('#plan-box').show();

                // Auto-save to My Plan
                var topic = sessionStorage.getItem('last_quiz_topic') || 'Quiz';
                savePlanToBackend('study_plan', topic, p, 'quiz', attemptId, null);
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error getting plan');
            })
            .always(function () { btnLoading(btn, false); });
    });

    // ================================================================
    // HISTORY PAGE
    // ================================================================

    function loadHistory() {
        get('/history/list.php').done(function (r) {
            // Conversations
            var html = '';
            if (!r.conversations || r.conversations.length === 0) {
                html = '<p style="color:#9ca3af; padding:.5rem;">No conversations yet. Start chatting with the AI Tutor!</p>';
            } else {
                $.each(r.conversations, function (i, c) {
                    html += '<div class="history-item" data-conv-id="' + c.id + '">';
                    html += '<div class="h-icon card-icon purple"><span class="material-icons">smart_toy</span></div>';
                    html += '<div class="h-info">';
                    html += '<div class="h-title">' + c.title + '</div>';
                    html += '<div class="h-meta">' + c.message_count + ' messages &middot; ' + c.updated_at + '</div>';
                    html += '</div>';
                    html += '<button class="btn-delete" data-del-type="conversation" data-del-id="' + c.id + '" title="Delete"><span class="material-icons">delete</span></button>';
                    html += '<div class="h-arrow"><span class="material-icons">arrow_forward</span></div>';
                    html += '</div>';
                });
            }
            $('#conv-list').html(html);

            // Quiz Attempts
            html = '';
            if (!r.attempts || r.attempts.length === 0) {
                html = '<p style="color:#9ca3af; padding:.5rem;">No quiz attempts yet.</p>';
            } else {
                $.each(r.attempts, function (i, a) {
                    html += '<div class="history-item" data-attempt-id="' + a.id + '">';
                    html += '<div class="h-icon card-icon orange"><span class="material-icons">quiz</span></div>';
                    html += '<div class="h-info">';
                    html += '<div class="h-title">' + a.topic + '</div>';
                    html += '<div class="h-meta">' + a.score + '/' + a.total + ' (' + a.percentage + '%) &middot; ' + a.created_at + '</div>';
                    html += '</div>';
                    html += '<button class="btn-delete" data-del-type="attempt" data-del-id="' + a.id + '" title="Delete"><span class="material-icons">delete</span></button>';
                    html += '</div>';
                });
            }
            $('#attempt-list').html(html);
        });
    }

    // Delete history item
    $(document).on('click', '.btn-delete', function (e) {
        e.stopPropagation(); // don't trigger the conversation click
        var btn = $(this);
        var type = btn.data('del-type');
        var id = btn.data('del-id');

        if (!confirm('Delete this ' + type + '? This cannot be undone.')) return;

        btn.prop('disabled', true).css('opacity', 0.5);
        post('/history/delete.php', { type: type, id: id })
            .done(function () {
                btn.closest('.history-item').fadeOut(300, function () { $(this).remove(); });
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error deleting');
                btn.prop('disabled', false).css('opacity', 1);
            });
    });

    // Click conversation → show chat replay
    $(document).on('click', '.history-item[data-conv-id]', function () {
        var convId = $(this).data('conv-id');
        var title = $(this).find('.h-title').text();

        // Hide list cards, show detail
        $('.main-content > .card').hide();
        $('#conv-detail-title').text(title);
        $('#conv-detail').show();

        // Load messages
        get('/chat/messages.php?id=' + convId).done(function (r) {
            var html = '';
            $.each(r.messages, function (i, msg) {
                var avatar = msg.role === 'assistant' ? 'AI' : 'You';
                var bubbleContent = msg.role === 'assistant' ? md(msg.content) : $('<div>').text(msg.content).html();
                html += '<div class="chat-message ' + msg.role + '">';
                html += '<div class="msg-avatar">' + avatar + '</div>';
                html += '<div class="msg-content"><div class="msg-bubble">' + bubbleContent + '</div></div>';
                html += '</div>';
            });
            $('#conv-messages').html(html);
        });
    });

    // Back button in conversation detail
    $(document).on('click', '#conv-back', function () {
        $('#conv-detail').hide();
        $('.main-content > .card').show();
    });

    // ================================================================
    // TUTOR NOTEPAD – Save notes from tutor page
    // ================================================================

    $(document).on('click', '#save-tutor-note', function () {
        var btn = $(this);
        var title = $('#tutor-note-title').val().trim();
        var content = $('#tutor-note-content').val().trim();

        if (!title && !content) {
            showMsg('#tutor-note-msg', 'Please write something first.', true);
            return;
        }

        btnLoading(btn, true);
        post('/notes/save.php', { title: title, content: content })
            .done(function (r) {
                showMsg('#tutor-note-msg', '<span class="material-icons" style="font-size:.9rem;vertical-align:middle">check_circle</span> Note saved!', false);
                // Clear after save
                setTimeout(function () {
                    $('#tutor-note-title').val('');
                    $('#tutor-note-content').val('');
                    showMsg('#tutor-note-msg', '', false);
                }, 1500);
            })
            .fail(function (xhr) {
                showMsg('#tutor-note-msg', xhr.responseJSON ? xhr.responseJSON.message : 'Error saving note', true);
            })
            .always(function () { btnLoading(btn, false); });
    });

    // ================================================================
    // NOTES PAGE – Load saved tutor notes on right panel
    // ================================================================

    function loadSavedNotes() {
        get('/notes/list.php').done(function (r) {
            var notes = r.notes;
            if (!notes || notes.length === 0) {
                $('#saved-notes-list').html(
                    '<div class="text-center" style="color:#9ca3af; padding:2rem;">'
                    + '<span class="material-icons" style="font-size:2.5rem;color:#d1d5db;">edit_note</span>'
                    + '<p style="margin-top:.75rem;">No notes yet. Use the notepad in AI Tutor to save notes here.</p>'
                    + '</div>'
                );
                return;
            }

            var html = '';
            $.each(notes, function (i, note) {
                html += '<div class="saved-note-card" data-note-id="' + note.id + '">';
                html += '<div class="note-card-title">' + $('<span>').text(note.title).html() + '</div>';
                html += '<div class="note-card-preview">' + $('<span>').text(note.content).html() + '</div>';
                html += '<div class="note-card-meta">';
                html += '<span>' + note.updated_at + '</span>';
                html += '<button class="note-delete-btn" data-note-del="' + note.id + '" title="Delete"><span class="material-icons" style="font-size:1rem">delete</span></button>';
                html += '</div>';
                html += '</div>';
            });
            $('#saved-notes-list').html(html);
        });
    }

    // View note in modal
    $(document).on('click', '.saved-note-card', function (e) {
        if ($(e.target).closest('.note-delete-btn').length) return;
        var title = $(this).find('.note-card-title').text();
        var preview = $(this).find('.note-card-preview').text();

        var html = '<div class="note-modal-overlay">'
            + '<div class="note-modal">'
            + '<div class="note-modal-header">'
            + '<h2>' + $('<span>').text(title).html() + '</h2>'
            + '<button class="note-modal-close"><span class="material-icons">close</span></button>'
            + '</div>'
            + '<div class="note-modal-body">' + $('<span>').text(preview).html() + '</div>'
            + '</div></div>';
        $('body').append(html);
    });

    $(document).on('click', '.note-modal-close, .note-modal-overlay', function (e) {
        if (e.target === this || $(this).hasClass('note-modal-close')) {
            $('.note-modal-overlay').remove();
        }
    });

    // Delete saved note
    $(document).on('click', '.note-delete-btn', function (e) {
        e.stopPropagation();
        var btn = $(this);
        var noteId = btn.data('note-del');
        if (!confirm('Delete this note?')) return;

        btn.prop('disabled', true);
        post('/notes/delete.php', { id: noteId })
            .done(function () {
                btn.closest('.saved-note-card').fadeOut(300, function () { $(this).remove(); });
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error deleting note');
                btn.prop('disabled', false);
            });
    });

    // ================================================================
    // SAVE PLAN TO BACKEND (shared helper)
    // ================================================================

    function savePlanToBackend(type, topic, content, source, attemptId, examId) {
        post('/saved-plan/save.php', {
            type: type,
            topic: topic,
            content: content,
            source: source,
            attempt_id: attemptId,
            exam_id: examId
        }).done(function () {
            // Silent save – show subtle notification
            var label = type === 'analysis' ? 'Analysis' : 'Study Plan';
            var toast = $('<div style="position:fixed;bottom:1.5rem;right:1.5rem;background:#10b981;color:#fff;padding:.65rem 1.25rem;border-radius:10px;font-size:.88rem;font-weight:500;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,.15);display:flex;align-items:center;gap:.4rem;">'
                + '<span class="material-icons" style="font-size:1rem">bookmark_added</span> '
                + label + ' saved to My Plan</div>');
            $('body').append(toast);
            setTimeout(function () { toast.fadeOut(400, function () { $(this).remove(); }); }, 3000);
        });
    }

    // ================================================================
    // MY PLAN PAGE
    // ================================================================

    var planFilter = 'all';

    function loadMyPlans() {
        get('/saved-plan/list.php').done(function (r) {
            var plans = r.plans;
            if (!plans || plans.length === 0) {
                $('#plans-list').html('');
                $('#no-plans').show();
                return;
            }
            $('#no-plans').hide();
            renderPlans(plans);
        });
    }

    function renderPlans(plans) {
        var filtered = plans;
        if (planFilter !== 'all') {
            filtered = plans.filter(function (p) { return p.type === planFilter; });
        }

        if (filtered.length === 0) {
            $('#plans-list').html('<div class="text-center mt-2" style="color:#9ca3af;">No ' + (planFilter === 'analysis' ? 'analyses' : 'study plans') + ' saved yet.</div>');
            return;
        }

        var html = '';
        $.each(filtered, function (i, plan) {
            var typeLabel = plan.type === 'analysis' ? 'Analysis' : 'Study Plan';
            var typeIcon = plan.type === 'analysis' ? 'bar_chart' : 'menu_book';

            html += '<div class="plan-card" data-plan-id="' + plan.id + '" data-plan-type="' + plan.type + '">';
            html += '<div class="plan-card-header">';
            html += '<span class="plan-type-badge ' + plan.type + '"><span class="material-icons" style="font-size:.85rem">' + typeIcon + '</span> ' + typeLabel + '</span>';
            html += '<button class="plan-delete-btn" data-plan-del="' + plan.id + '" title="Delete"><span class="material-icons">delete</span></button>';
            html += '</div>';
            html += '<div class="plan-topic">' + $('<span>').text(plan.topic).html() + '</div>';
            html += '<div class="plan-source">From: ' + plan.source + '</div>';
            html += '<div class="plan-body">' + renderPlanContent(plan.type, plan.content) + '</div>';
            html += '<div class="plan-date">' + plan.created_at + '</div>';
            html += '</div>';
        });
        $('#plans-list').html(html);
    }

    function renderPlanContent(type, content) {
        if (!content) return '';
        var html = '';

        if (type === 'analysis') {
            if (content.weaknesses && content.weaknesses.length) {
                html += '<h3>Weaknesses</h3><ul>';
                $.each(content.weaknesses, function (i, w) { html += '<li>' + w + '</li>'; });
                html += '</ul>';
            }
            if (content.insights && content.insights.length) {
                html += '<h3>Insights</h3><ul>';
                $.each(content.insights, function (i, w) { html += '<li>' + w + '</li>'; });
                html += '</ul>';
            }
            if (content.recommendations && content.recommendations.length) {
                html += '<h3>Recommendations</h3><ul>';
                $.each(content.recommendations, function (i, w) { html += '<li>' + w + '</li>'; });
                html += '</ul>';
            }
        } else if (type === 'study_plan') {
            if (content.title) html += '<h3>' + content.title + '</h3>';
            if (content.days && content.days.length) {
                $.each(content.days, function (i, day) {
                    html += '<div style="margin-bottom:.75rem;"><strong>Day ' + day.day + ':</strong> ' + day.focus + '<ul>';
                    $.each(day.tasks || [], function (j, t) { html += '<li>' + t + '</li>'; });
                    html += '</ul></div>';
                });
            }
        }
        return html;
    }

    // Plan filter tabs
    $(document).on('click', '.plan-tab', function () {
        $('.plan-tab').removeClass('active');
        $(this).addClass('active');
        planFilter = $(this).data('filter');
        loadMyPlans();
    });

    // Delete plan
    $(document).on('click', '.plan-delete-btn', function (e) {
        e.stopPropagation();
        var btn = $(this);
        var planId = btn.data('plan-del');
        if (!confirm('Delete this saved plan?')) return;

        btn.prop('disabled', true);
        post('/saved-plan/delete.php', { id: planId })
            .done(function () {
                btn.closest('.plan-card').fadeOut(300, function () {
                    $(this).remove();
                    if ($('.plan-card').length === 0) {
                        $('#no-plans').show();
                    }
                });
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error deleting plan');
                btn.prop('disabled', false);
            });
    });

    // ── Public API ───────────────────────────────────────────────
    return {
        loadDashboard: loadDashboard,
        loadHistory: loadHistory,
        loadResult: loadResult,
        initChat: initChat,
        loadSavedNotes: loadSavedNotes,
        loadMyPlans: loadMyPlans
    };

})(jQuery);
