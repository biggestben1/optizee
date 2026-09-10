import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';

import '../config/api_config.dart';
import '../providers/auth_provider.dart';
import '../services/api_client.dart';
import '../theme/app_theme.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> with SingleTickerProviderStateMixin {
  late final TabController _tabs = TabController(length: 2, vsync: this);
  final _codeCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  final _serverCtrl = TextEditingController(text: ApiConfig.defaultBaseUrl);
  bool _showServer = false;

  @override
  void initState() {
    super.initState();
    final api = context.read<ApiClient>();
    _serverCtrl.text = api.baseUrl;
  }

  @override
  void dispose() {
    _tabs.dispose();
    _codeCtrl.dispose();
    _emailCtrl.dispose();
    _passwordCtrl.dispose();
    _serverCtrl.dispose();
    super.dispose();
  }

  Future<void> _saveServer() async {
    await context.read<ApiClient>().setBaseUrl(_serverCtrl.text.trim());
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Server saved')),
    );
    setState(() => _showServer = false);
  }

  Future<void> _loginCode() async {
    final ok = await context.read<AuthProvider>().loginWithCode(_codeCtrl.text.trim());
    if (!ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(context.read<AuthProvider>().error ?? 'Login failed')),
      );
    }
  }

  Future<void> _loginEmail() async {
    final ok = await context.read<AuthProvider>().loginWithEmail(
          _emailCtrl.text.trim(),
          _passwordCtrl.text,
        );
    if (!ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(context.read<AuthProvider>().error ?? 'Login failed')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Color(0xFF2A2118), AppTheme.bg],
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(22),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 440),
                child: Column(
                  children: [
                    Container(
                      width: 88,
                      height: 88,
                      decoration: BoxDecoration(
                        color: AppTheme.surface,
                        borderRadius: BorderRadius.circular(22),
                        border: Border.all(color: AppTheme.border),
                      ),
                      padding: const EdgeInsets.all(12),
                      child: Image.asset(
                        'assets/logo.jpg',
                        errorBuilder: (_, __, ___) => const Icon(Icons.storefront, size: 42, color: AppTheme.accent),
                      ),
                    ),
                    const SizedBox(height: 18),
                    const Text(
                      'Optizee Staff',
                      style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Cashier & Kitchen',
                      style: TextStyle(color: AppTheme.muted),
                    ),
                    const SizedBox(height: 28),
                    Container(
                      decoration: BoxDecoration(
                        color: AppTheme.surface.withValues(alpha: 0.95),
                        borderRadius: BorderRadius.circular(22),
                        border: Border.all(color: AppTheme.border),
                      ),
                      padding: const EdgeInsets.all(18),
                      child: Column(
                        children: [
                          Container(
                            decoration: BoxDecoration(
                              color: AppTheme.surface2,
                              borderRadius: BorderRadius.circular(14),
                            ),
                            child: TabBar(
                              controller: _tabs,
                              indicator: BoxDecoration(
                                color: AppTheme.accent.withValues(alpha: 0.2),
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: AppTheme.accent.withValues(alpha: 0.45)),
                              ),
                              indicatorSize: TabBarIndicatorSize.tab,
                              labelColor: AppTheme.accent,
                              unselectedLabelColor: AppTheme.muted,
                              dividerColor: Colors.transparent,
                              tabs: const [
                                Tab(text: 'Quick Code'),
                                Tab(text: 'Email'),
                              ],
                            ),
                          ),
                          const SizedBox(height: 18),
                          SizedBox(
                            height: 210,
                            child: TabBarView(
                              controller: _tabs,
                              children: [
                                Column(
                                  children: [
                                    TextField(
                                      controller: _codeCtrl,
                                      keyboardType: TextInputType.number,
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(
                                        fontSize: 28,
                                        fontWeight: FontWeight.w800,
                                        letterSpacing: 10,
                                      ),
                                      inputFormatters: [
                                        FilteringTextInputFormatter.digitsOnly,
                                        LengthLimitingTextInputFormatter(4),
                                      ],
                                      decoration: const InputDecoration(
                                        hintText: '••••',
                                        labelText: '4-digit staff code',
                                      ),
                                      onSubmitted: (_) => _loginCode(),
                                    ),
                                    const Spacer(),
                                    FilledButton(
                                      onPressed: auth.busy ? null : _loginCode,
                                      child: auth.busy
                                          ? const SizedBox(
                                              width: 22,
                                              height: 22,
                                              child: CircularProgressIndicator(strokeWidth: 2),
                                            )
                                          : const Text('Sign in'),
                                    ),
                                  ],
                                ),
                                Column(
                                  children: [
                                    TextField(
                                      controller: _emailCtrl,
                                      keyboardType: TextInputType.emailAddress,
                                      decoration: const InputDecoration(labelText: 'Email'),
                                    ),
                                    const SizedBox(height: 10),
                                    TextField(
                                      controller: _passwordCtrl,
                                      obscureText: true,
                                      decoration: const InputDecoration(labelText: 'Password'),
                                      onSubmitted: (_) => _loginEmail(),
                                    ),
                                    const Spacer(),
                                    FilledButton(
                                      onPressed: auth.busy ? null : _loginEmail,
                                      child: auth.busy
                                          ? const SizedBox(
                                              width: 22,
                                              height: 22,
                                              child: CircularProgressIndicator(strokeWidth: 2),
                                            )
                                          : const Text('Sign in'),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextButton.icon(
                      onPressed: () => setState(() => _showServer = !_showServer),
                      icon: const Icon(Icons.settings_ethernet, size: 18),
                      label: const Text('Server settings'),
                      style: TextButton.styleFrom(foregroundColor: AppTheme.muted),
                    ),
                    if (_showServer) ...[
                      const SizedBox(height: 8),
                      TextField(
                        controller: _serverCtrl,
                        decoration: const InputDecoration(
                          labelText: 'API base URL',
                          hintText: 'http://192.168.x.x/api',
                        ),
                      ),
                      const SizedBox(height: 10),
                      OutlinedButton(
                        onPressed: _saveServer,
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppTheme.text,
                          side: const BorderSide(color: AppTheme.border),
                          minimumSize: const Size.fromHeight(48),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        child: const Text('Save server'),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
