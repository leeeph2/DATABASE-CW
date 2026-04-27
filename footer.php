</div> <footer class="footer-global">
    <div class="nav-container" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">&copy; <?php echo date("Y"); ?> Internship Result Management System</p>
            <p style="margin: 0; font-size: 0.85rem; color: var(--text-main); font-weight: 600;">University of Nottingham Malaysia</p>
        </div>
        
        <?php if (isset($_SESSION['username'])): ?>
        <div style="text-align: right;">
            <span style="text-transform: uppercase; letter-spacing: 1px; font-size: 0.7rem; font-weight: 700; color: var(--text-muted);">Active Session</span>
            <p style="margin-top: 4px; font-size: 0.85rem; color: var(--text-main);">
                <strong style="color: var(--primary-blue);"><?php echo htmlspecialchars($_SESSION['username']); ?></strong> 
                (<?php echo htmlspecialchars($_SESSION['role']); ?>)
            </p>
        </div>
        <?php endif; ?>
    </div>
</footer>

<script src="script.js?v=<?php echo time(); ?>"></script>
</body>
</html>