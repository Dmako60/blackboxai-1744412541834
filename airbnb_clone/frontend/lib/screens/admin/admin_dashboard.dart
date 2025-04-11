import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/loading_indicator.dart';

class AdminDashboard extends StatefulWidget {
  @override
  _AdminDashboardState createState() => _AdminDashboardState();
}

class _AdminDashboardState extends State<AdminDashboard> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool isLoading = true;
  Map<String, dynamic> dashboardStats = {};
  List<Map<String, dynamic>> agents = [];
  List<Map<String, dynamic>> properties = [];
  int currentAgentPage = 1;
  int currentPropertyPage = 1;
  String? searchQuery;
  
  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _loadDashboardData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadDashboardData() async {
    try {
      final response = await http.get(
        Uri.parse('http://localhost/backend/admin/dashboard-stats'),
        headers: {
          'Authorization': 'Bearer YOUR_TOKEN_HERE', // Replace with actual token
        },
      );

      if (response.statusCode == 200) {
        setState(() {
          dashboardStats = json.decode(response.body)['data'];
          isLoading = false;
        });
        
        // Load initial data for both tabs
        _loadAgents();
        _loadProperties();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to load dashboard data')),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('An error occurred')),
      );
    }
  }

  Future<void> _loadAgents() async {
    try {
      final response = await http.get(
        Uri.parse('http://localhost/backend/admin/agents?page=$currentAgentPage'),
        headers: {
          'Authorization': 'Bearer YOUR_TOKEN_HERE', // Replace with actual token
        },
      );

      if (response.statusCode == 200) {
        setState(() {
          agents = List<Map<String, dynamic>>.from(json.decode(response.body)['data']['agents']);
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to load agents')),
      );
    }
  }

  Future<void> _loadProperties() async {
    try {
      final response = await http.get(
        Uri.parse('http://localhost/backend/admin/properties?page=$currentPropertyPage'),
        headers: {
          'Authorization': 'Bearer YOUR_TOKEN_HERE', // Replace with actual token
        },
      );

      if (response.statusCode == 200) {
        setState(() {
          properties = List<Map<String, dynamic>>.from(json.decode(response.body)['data']['properties']);
        });
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to load properties')),
      );
    }
  }

  Future<void> _approveAgent(int agentId) async {
    try {
      final response = await http.post(
        Uri.parse('http://localhost/backend/admin/approve-agent'),
        headers: {
          'Authorization': 'Bearer YOUR_TOKEN_HERE', // Replace with actual token
          'Content-Type': 'application/json',
        },
        body: json.encode({'agent_id': agentId}),
      );

      if (response.statusCode == 200) {
        _loadAgents();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Agent approved successfully')),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to approve agent')),
      );
    }
  }

  Future<void> _deleteAgent(int agentId) async {
    try {
      final response = await http.delete(
        Uri.parse('http://localhost/backend/admin/delete-agent'),
        headers: {
          'Authorization': 'Bearer YOUR_TOKEN_HERE', // Replace with actual token
          'Content-Type': 'application/json',
        },
        body: json.encode({'agent_id': agentId}),
      );

      if (response.statusCode == 200) {
        _loadAgents();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Agent deleted successfully')),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to delete agent')),
      );
    }
  }

  Widget _buildDashboardOverview() {
    return GridView.count(
      crossAxisCount: 2,
      padding: EdgeInsets.all(16),
      childAspectRatio: 1.5,
      children: [
        _buildStatCard(
          'Total Agents',
          dashboardStats['total_agents']?.toString() ?? '0',
          Icons.people,
          Colors.blue,
        ),
        _buildStatCard(
          'Pending Agents',
          dashboardStats['pending_agents']?.toString() ?? '0',
          Icons.person_add,
          Colors.orange,
        ),
        _buildStatCard(
          'Total Properties',
          dashboardStats['total_properties']?.toString() ?? '0',
          Icons.home,
          Colors.green,
        ),
        _buildStatCard(
          'Total Earnings',
          '\$${dashboardStats['total_earnings']?.toString() ?? '0'}',
          Icons.attach_money,
          Colors.purple,
        ),
      ],
    );
  }

  Widget _buildStatCard(String title, String value, IconData icon, Color color) {
    return Card(
      elevation: 4,
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 48, color: color),
            SizedBox(height: 8),
            Text(
              title,
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            SizedBox(height: 4),
            Text(
              value,
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAgentsList() {
    return Column(
      children: [
        Padding(
          padding: EdgeInsets.all(16),
          child: TextField(
            decoration: InputDecoration(
              hintText: 'Search agents...',
              prefixIcon: Icon(Icons.search),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
            onChanged: (value) {
              setState(() {
                searchQuery = value;
              });
              // Implement debounced search
            },
          ),
        ),
        Expanded(
          child: ListView.builder(
            itemCount: agents.length,
            itemBuilder: (context, index) {
              final agent = agents[index];
              return Card(
                margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                child: ListTile(
                  leading: CircleAvatar(
                    child: Text(agent['name'][0].toUpperCase()),
                  ),
                  title: Text(agent['name']),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(agent['email']),
                      Text(
                        'Subscription: ${agent['subscription_type']} • Properties: ${agent['property_count']}',
                        style: TextStyle(fontSize: 12),
                      ),
                    ],
                  ),
                  trailing: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (agent['status'] == 'pending')
                        IconButton(
                          icon: Icon(Icons.check_circle_outline),
                          color: Colors.green,
                          onPressed: () => _approveAgent(agent['id']),
                          tooltip: 'Approve Agent',
                        ),
                      IconButton(
                        icon: Icon(Icons.delete_outline),
                        color: Colors.red,
                        onPressed: () => _deleteAgent(agent['id']),
                        tooltip: 'Delete Agent',
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildPropertiesList() {
    return Column(
      children: [
        Padding(
          padding: EdgeInsets.all(16),
          child: TextField(
            decoration: InputDecoration(
              hintText: 'Search properties...',
              prefixIcon: Icon(Icons.search),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
            onChanged: (value) {
              // Implement property search
            },
          ),
        ),
        Expanded(
          child: ListView.builder(
            itemCount: properties.length,
            itemBuilder: (context, index) {
              final property = properties[index];
              return Card(
                margin: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                child: ListTile(
                  leading: property['image_url'] != null
                      ? Image.network(
                          property['image_url'],
                          width: 60,
                          height: 60,
                          fit: BoxFit.cover,
                        )
                      : Icon(Icons.home, size: 40),
                  title: Text(property['title']),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(property['location']),
                      Text(
                        '\$${property['price_per_night']}/night',
                        style: TextStyle(
                          color: Colors.green,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  trailing: PopupMenuButton(
                    itemBuilder: (context) => [
                      PopupMenuItem(
                        value: 'view',
                        child: ListTile(
                          leading: Icon(Icons.visibility),
                          title: Text('View Details'),
                        ),
                      ),
                      PopupMenuItem(
                        value: 'edit',
                        child: ListTile(
                          leading: Icon(Icons.edit),
                          title: Text('Edit'),
                        ),
                      ),
                      PopupMenuItem(
                        value: 'delete',
                        child: ListTile(
                          leading: Icon(Icons.delete),
                          title: Text('Delete'),
                          textColor: Colors.red,
                          iconColor: Colors.red,
                        ),
                      ),
                    ],
                    onSelected: (value) {
                      switch (value) {
                        case 'view':
                          // Navigate to property details
                          break;
                        case 'edit':
                          // Navigate to edit property
                          break;
                        case 'delete':
                          // Show delete confirmation
                          break;
                      }
                    },
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Scaffold(
        body: Center(child: LoadingIndicator()),
      );
    }

    return Scaffold(
      appBar: CustomAppBar(
        title: Text('Admin Dashboard'),
        bottom: TabBar(
          controller: _tabController,
          tabs: [
            Tab(text: 'Overview'),
            Tab(text: 'Agents'),
            Tab(text: 'Properties'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildDashboardOverview(),
          _buildAgentsList(),
          _buildPropertiesList(),
        ],
      ),
      floatingActionButton: _tabController.index == 2
          ? FloatingActionButton(
              onPressed: () {
                // Navigate to add property screen
              },
              child: Icon(Icons.add),
              tooltip: 'Add Property',
            )
          : null,
    );
  }
}
